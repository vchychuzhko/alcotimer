define([
    'jquery',
], function ($) {
    'use strict'

    const NUMBER_OF_PEAKS = 2048;

    return {
        analyzerNode: null,
        canvas: null,
        data: [],
        initialized: false,
        // @todo: requirejs objects are global and reused, do something about it. Also this property seems to be useless

        /**
         * Init audio spectrum visualiser.
         * @param {HTMLMediaElement} audio
         * @param {HTMLElement} canvas
         * @param {number} numberOfPeaks
         */
        init: function (audio, canvas, numberOfPeaks = NUMBER_OF_PEAKS) {
            let audioContext = new (window.AudioContext || window.webkitAudioContext)();
            let audioSource = audioContext.createMediaElementSource(audio);

            this.analyzerNode = audioContext.createAnalyser();
            this.analyzerNode.fftSize = numberOfPeaks;

            audioSource.connect(this.analyzerNode);
            audioSource.connect(audioContext.destination);

            this.data = new Uint8Array(this.analyzerNode.frequencyBinCount);
            this.canvas = canvas;

            this.initialized = true;
        },

        /**
         * Request next data frame and draw it.
         */
        render: function () {
            this.analyzerNode.getByteFrequencyData(this.data);

            this._draw();
        },

        /**
         * Render audio spectrum.
         * @private
         */
        _draw: function () {
            let context = this.canvas.getContext('2d'); // @todo: store in property
            let height = this.canvas.height;
            let width = this.canvas.width;
            let average = (this.data.reduce((a, b) => a + b, 0) / this.analyzerNode.frequencyBinCount) / 255;
            let radius = height / 4 * (average / 2 + 1); // @todo: wtf is +1?

            context.clearRect(0, 0, width, height);

            context.strokeStyle = 'white';
            context.lineWidth = 2;
            context.beginPath();
            context.arc(width / 2, height / 2, radius, 0, 2 * Math.PI);
            context.stroke();
            context.closePath();

            $.each(this.data, (index, value) => {
                value = value * 2.5 - 255;
                if (value < 0) value = 0; // @TODO: Add complex scale/slice/filter modifications

                value = (value / 255) * (height / 8);

                let currentAngle = 360 * (index / this.analyzerNode.frequencyBinCount);
                let x1 = (width / 2) + radius * Math.cos(this._degreesToRadians(currentAngle - 90));
                let y1 = (height / 2) + radius * Math.sin(this._degreesToRadians(currentAngle - 90));
                let x2 = (width / 2) + (radius + value) * Math.cos(this._degreesToRadians(currentAngle - 90));
                let y2 = (height / 2) + (radius + value) * Math.sin(this._degreesToRadians(currentAngle - 90));

                context.lineWidth = 2;
                context.beginPath();
                context.moveTo(x1, y1);
                context.lineTo(x2, y2);
                context.stroke();
                context.closePath();
            });
        },

        /**
         * Convert degree value to radians.
         * @param {number} degrees
         * @returns {number}
         * @private
         */
        _degreesToRadians: function (degrees) {
            return (Math.PI / 180) * degrees;
        },
    };
});
