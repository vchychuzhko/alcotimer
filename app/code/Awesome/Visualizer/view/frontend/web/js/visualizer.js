define([
    'jquery',
], function ($) {
    'use strict'

    const NUMBER_OF_PEAKS = 2048;

    class Visualizer {
        _analyzerNode;
        _canvas;
        _data;

        /**
         * Audio spectrum visualiser constructor.
         * @param {HTMLMediaElement} audio
         * @param {HTMLElement} canvas
         * @param {number} numberOfPeaks
         */
        constructor (audio, canvas, numberOfPeaks = NUMBER_OF_PEAKS) {
            let audioContext = new (window.AudioContext || window.webkitAudioContext)();
            let audioSource = audioContext.createMediaElementSource(audio);

            this._analyzerNode = audioContext.createAnalyser();
            this._analyzerNode.fftSize = numberOfPeaks;

            audioSource.connect(this._analyzerNode);
            audioSource.connect(audioContext.destination);

            this._data = new Uint8Array(this._analyzerNode.frequencyBinCount);
            this._canvas = canvas;
        }

        /**
         * Request next data frame and draw it.
         */
        render () {
            this._analyzerNode.getByteFrequencyData(this._data);

            this._draw();
        }

        /**
         * Render audio spectrum.
         * @private
         */
        _draw () {
            let context = this._canvas.getContext('2d');
            let height = this._canvas.height;
            let width = this._canvas.width;
            let average = (this._data.reduce((a, b) => a + b, 0) / this._analyzerNode.frequencyBinCount) / 255;
            let radius = height / 4 * (average / 2 + 1);

            context.clearRect(0, 0, width, height);

            context.strokeStyle = 'white';
            context.lineWidth = 2;
            context.beginPath();
            context.arc(width / 2, height / 2, radius, 0, 2 * Math.PI);
            context.stroke();
            context.closePath();

            $.each(this._data, (index, value) => {
                value = value * 2.5 - 255;
                if (value < 0) value = 0; // @TODO: Add complex scale/slice/filter modifications

                value = (value / 255) * (height / 8);

                let currentAngle = 360 * (index / this._analyzerNode.frequencyBinCount);
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
        }

        /**
         * Convert degree value to radians.
         * @param {number} degrees
         * @returns {number}
         * @private
         */
        _degreesToRadians (degrees) {
            return (Math.PI / 180) * degrees;
        }
    }

    return {
        /**
         * Init audio spectrum visualiser.
         * @param {HTMLMediaElement} audio
         * @param {HTMLElement} canvas
         * @param {number} numberOfPeaks
         * @returns {Visualizer}
         */
        init: function (audio, canvas, numberOfPeaks = NUMBER_OF_PEAKS) {
            return new Visualizer(audio, canvas, numberOfPeaks)
        },
    };
});
