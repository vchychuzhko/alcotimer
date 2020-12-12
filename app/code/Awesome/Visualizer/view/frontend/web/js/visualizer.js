define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict'

    const RUNNING_STATE = 'running',
          PAUSED_STATE = 'paused',
          STOPPED_STATE = 'stopped';

    $.widget('awesome.visualizer', {
        options: {
            canvasSelector: '#canvas',
            playerSelector: '#player',
            timecodeSelector: '#timecode',
            tracknameSelector: '#trackname',
        },

        canvas: null,
        player: null,
        timecode: null,
        trackname: null,

        data: [],
        analyzerNode: null,
        state: null,

        /**
         * Constructor.
         */
        _create: function () {
            this.initFields();
            this.initBindings();
            this.calculateCanvasSize();
        },

        /**
         * Init widget fields.
         */
        initFields: function () {
            this.player = this.element.get(0).querySelector(this.options.playerSelector);

            if ('ontouchstart' in document.documentElement) {
                this.player.classList.add('nohide');
            }
            this.canvas = this.element.get(0).querySelector(this.options.canvasSelector);
            this.timecode = this.element.get(0).querySelector(this.options.timecodeSelector);
            this.trackname = this.element.get(0).querySelector(this.options.tracknameSelector);
        },

        /**
         * Init event listeners.
         */
        initBindings: function () {
            window.addEventListener('resize', this.calculateCanvasSize.bind(this));

            document.addEventListener('dragover', function (event) {
                event.preventDefault();
                event.stopPropagation();
            });

            document.addEventListener('drop', function (event) {
                event.preventDefault();
                event.stopPropagation();

                let file = event.dataTransfer.files[0],
                    fileName = file.name.replace(/\.[^/.]+$/, '');

                this.updateTrackname(fileName);
                this.player.setAttribute('src', URL.createObjectURL(file));

                this.player.play();
            }.bind(this));
            // @TODO: Check lock screen play

            this.player.addEventListener('timeupdate', this.updateTimecode.bind(this));

            this.player.addEventListener('play', this.play.bind(this));

            this.player.addEventListener('pause', this.pause.bind(this));

            document.addEventListener('keyup', function (event) {
                if (!event.target.matches(this.options.playerSelector)) {
                    this.handlePlayerControls(event);
                }
            }.bind(this));
        },

        /**
         * Update audio track name.
         * @param {string} trackName
         */
        updateTrackname: function (trackName) {
            this.trackname.innerHTML = trackName;
        },

        /**
         * Update time code according to the player.
         * @param {object} event
         */
        updateTimecode: function (event) {
            let totalSeconds = event.currentTarget.currentTime,
                hours = ('00' + Math.floor(totalSeconds / 3600)).substr(-2, 2),
                minutes = ('00' + Math.floor(totalSeconds % 3600 / 60)).substr(-2, 2),
                seconds = ('00' + Math.floor(totalSeconds % 60)).substr(-2, 2);

            this.timecode.innerHTML = `${hours}:${minutes}:${seconds}`;
        },

        /**
         * Init sound visualiser.
         */
        initVisualizer: function () {
            let audioContext = new (window.AudioContext || window.webkitAudioContext)(),
                audioSource = audioContext.createMediaElementSource(this.player);

            this.analyzerNode = audioContext.createAnalyser();
            this.analyzerNode.fftSize = 2048;

            audioSource.connect(this.analyzerNode);
            audioSource.connect(audioContext.destination);

            this.data = new Uint8Array(this.analyzerNode.frequencyBinCount);
        },

        /**
         * Start/resume audio visualization.
         * Init visualizer if was not yet.
         */
        play: function () {
            if (!this.analyzerNode) {
                this.initVisualizer();
            }

            if (this.state !== RUNNING_STATE) {
                this.state = RUNNING_STATE;

                this.run()
            }
        },

        /**
         * Pause audio visualization.
         */
        pause: function () {
            this.state = PAUSED_STATE;

            setTimeout(function () {
                // Timeout is needed to have "fade" effect on canvas
                // Extra state is needed to solve goTo issue for audio element
                if (this.state === PAUSED_STATE) {
                    this.state = STOPPED_STATE;
                }
            }.bind(this), 1000);
        },

        /**
         * Process data and request next frame.
         */
        run: function () {
            this.analyzerNode.getByteFrequencyData(this.data);

            this.render();

            if (this.state !== STOPPED_STATE) {
                requestAnimationFrame(this.run.bind(this));
            }
        },

        /**
         * Render audio spectrum.
         */
        render: function () {
            let context = this.canvas.getContext('2d'),
                height = this.canvas.height,
                width = this.canvas.width,
                radius = height / 4;

            context.clearRect(0, 0, width, height);

            context.strokeStyle = 'white';
            context.lineWidth = 2;
            context.beginPath();
            context.arc(width / 2, height / 2, radius, 0, 2 * Math.PI);
            context.stroke();
            context.closePath();

            $.each(this.data, function (index, value) {
                value = (value / 255) * (radius / 2);

                let currentAngle = 360 * (index / this.analyzerNode.frequencyBinCount),
                    x1 = (width / 2) + radius * Math.cos(this.degreesToRadians(currentAngle - 90)),
                    x2 = (width / 2) + (radius + value) * Math.cos(this.degreesToRadians(currentAngle - 90)),
                    y1 = (height / 2) + radius * Math.sin(this.degreesToRadians(currentAngle - 90)),
                    y2 = (height / 2) + (radius + value) * Math.sin(this.degreesToRadians(currentAngle - 90));

                context.lineWidth = 2;
                context.beginPath();
                context.moveTo(x1, y1);
                context.lineTo(x2, y2);
                context.stroke();
                context.closePath();
            }.bind(this));
        },

        /**
         * Recalculate canvas size to keep it squared.
         */
        calculateCanvasSize: function () {
            let size;

            if (window.innerWidth > window.innerHeight) {
                size = Math.round(Math.min(window.innerHeight * 0.9, window.innerWidth * 0.4));
                this.element.get(0).classList.remove('vertical');
            } else if (window.innerHeight > window.innerWidth) {
                size = Math.round(Math.min(window.innerWidth * 0.9, window.innerHeight * 0.6));
                this.element.get(0).classList.add('vertical');
            }
            this.canvas.style.height = size + 'px';
            this.canvas.style.width = size + 'px';

            this.canvas.height = size;
            this.canvas.width = size;
        },

        /**
         * Handle player controls buttons pressing.
         * @param {object} event
         */
        handlePlayerControls: function (event) {
            switch (event.key) {
                case ' ':
                    event.preventDefault();

                    if (!this.player.paused) {
                        this.player.pause();
                    } else {
                        this.player.play();
                    }
                    break;
                case 'ArrowLeft':
                    event.preventDefault();

                    this.player.currentTime = Math.max(this.player.currentTime - 10, 0);
                    break;
                case 'ArrowRight':
                    event.preventDefault();

                    this.player.currentTime = Math.min(this.player.currentTime + 10, Math.floor(this.player.duration));
                    break;
                case 'ArrowUp':
                    event.preventDefault();

                    this.player.volume = Math.min(this.player.volume + 0.1, 1);
                    break;
                case 'ArrowDown':
                    event.preventDefault();

                    this.player.volume = Math.max(this.player.volume - 0.1, 0);
                    break;
                case '0':
                    event.preventDefault();

                    this.player.currentTime = 0;
                    break;
                case 'f':
                    event.preventDefault();

                    // @TODO: Implement fullscreen toggling (hiding header and footer) functionality
                    break;
                case 'm':
                    event.preventDefault();

                    this.player.muted = !this.player.muted;
                    break;
            }
        },

        /**
         * Convert degree value to radians.
         * @param {number} angle
         * @return {number}
         */
        degreesToRadians: function (angle) {
            return (Math.PI / 180) * angle;
        },
    });
});
