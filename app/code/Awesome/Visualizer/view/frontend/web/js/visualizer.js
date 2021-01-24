define([
    'jquery',
], function ($) {
    'use strict'

    const NUMBER_OF_PEAKS = 2048;

    return {
        analyzerNode: null,
        $canvas: null,
        data: [],
        initialized: false,

        /**
         * Init audio spectrum visualiser.
         * @param {HTMLMediaElement} $audio
         * @param {HTMLElement} $canvas
         * @param {number} numberOfPeaks
         */
        init: function ($audio, $canvas, numberOfPeaks = NUMBER_OF_PEAKS) {
            let audioContext = new (window.AudioContext || window.webkitAudioContext)(),
                audioSource = audioContext.createMediaElementSource($audio);

            this.analyzerNode = audioContext.createAnalyser();
            this.analyzerNode.fftSize = numberOfPeaks;

            audioSource.connect(this.analyzerNode);
            audioSource.connect(audioContext.destination);

            this.data = new Uint8Array(this.analyzerNode.frequencyBinCount);
            this.$canvas = $canvas;

            this.initialized = true;
        },

        /**
         * Request next data frame and paint it.
         */
        render: function () {
            this.analyzerNode.getByteFrequencyData(this.data);

            this.paint();
        },

        /**
         * Render audio spectrum.
         */
        paint: function () {
            let context = this.$canvas.getContext('2d'),
                height = this.$canvas.height,
                width = this.$canvas.width,
                average = (this.data.reduce((a, b) => a + b, 0) / this.analyzerNode.frequencyBinCount) / 255,
                radius = height / 4 * (average / 2 + 1);

            context.clearRect(0, 0, width, height);

            context.strokeStyle = 'white';
            context.lineWidth = 2;
            context.beginPath();
            context.arc(width / 2, height / 2, radius, 0, 2 * Math.PI);
            context.stroke();
            context.closePath();

            $.each(this.data, function (index, value) {
                value = value * 2.5 - 255;
                if (value < 0) value = 0; // @TODO: Add complex scale/slice/filter modifications

                value = (value / 255) * (height / 8);

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
         * Convert degree value to radians.
         * @param {number} angle
         * @return {number}
         */
        degreesToRadians: function (angle) {
            return (Math.PI / 180) * angle;
        },
    };
});
