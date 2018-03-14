<?php /* Template Name: CustomPageT1 */ ?>

<html>

<head>
    <style>
        html, body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>

<canvas id="canvas" style="height: 100%; width: 100%; display: block"></canvas>

<script>
    // canvas stuff
    let canvas = document.getElementById("canvas");
    let ctx = canvas.getContext("2d");
    let maxWidth = canvas.width = window.innerWidth;
    let maxHeight = canvas.height = window.innerHeight;
    ctx.font = '24px serif';
    let winScreen = false;

    // fps and timing stuff
    const maxFPS = 60;
    let delta = 0;
    let fps = 60;
    let lastFrameTimeMs = 0;
    let timestep = 1000 / 60;
    let framesThisSecond = 0;
    let lastFpsUpdate = 0;

    // paddle stuff
    const PADDLE_WIDTH = 10;
    const PADDLE_HEIGHT = 100;
    const PADDLE_SPEED = 0.18;
    const PADDLE_EFFECT = 0.03;
    const PADDLE_WIN_SCORE = 5;
    // only affects computerMovement
    const PADDLE_INACCURACY = 25;
    let leftPaddle = new function () {
        this.width = PADDLE_WIDTH;
        this.height = PADDLE_HEIGHT;
        this.speed = PADDLE_SPEED;
        this.effect = PADDLE_EFFECT;
        this.inaccuracy = PADDLE_INACCURACY;
        this.winningScore = PADDLE_WIN_SCORE;
        this.x = 0;
        this.y = 0;
        this.score = 0;
        this.center = function () {
            return this.y + this.height / 2
        };
    };
    let rightPaddle = new function () {
        this.width = PADDLE_WIDTH;
        this.height = PADDLE_HEIGHT;
        this.speed = PADDLE_SPEED;
        this.effect = PADDLE_EFFECT;
        this.inaccuracy = PADDLE_INACCURACY;
        this.winningScore = PADDLE_WIN_SCORE;
        this.x = maxWidth - this.width;
        this.y = 0;
        this.score = 0;
        this.center = function () {
            return this.y + this.height / 2
        };
        this.resize = function () {
            this.x = maxWidth - this.width;
        };
    };

    // ball stuff
    const BALL_SPEED_RANGE = 0.50;
    let ball = new function () {
        this.x = maxWidth / 2;
        this.y = maxHeight / 2;
        this.radius = 10;
        this.speedRange = BALL_SPEED_RANGE;
        this.generateRandomSpeed = function () {
            return Math.random() * (this.speedRange - -this.speedRange) + -this.speedRange;
        };
        this.speedX = this.generateRandomSpeed();
        this.speedY = this.generateRandomSpeed();
    };

    // what to do when window size changes
    window.onresize = () => {
        maxWidth = canvas.width = window.innerWidth;
        maxHeight = canvas.height = window.innerHeight;
        rightPaddle.resize();
        ctx.font = '24px serif';
    };

    // mouse stuff
    let mouseY = 0;
    canvas.addEventListener("mousemove", function (evt) {
        mouseY = evt.clientY;
    });

    function mouseMovement(paddle) {
        let center = paddle.center();
        if (center < mouseY) {
            paddle.y += paddle.speed * delta;
        } else if (center > mouseY) {
            paddle.y -= paddle.speed * delta;
        }
    }

    // computer Moving paddle
    function computerMovement(paddle) {
        let center = paddle.center();
        if (center < ball.y + paddle.inaccuracy) {
            paddle.y += paddle.speed * delta;
        } else if (center > ball.y - paddle.inaccuracy) {
            paddle.y -= paddle.speed * delta;
        }
    }

    function ballReset() {
        if (leftPaddle.score >= leftPaddle.winningScore
            || rightPaddle.score >= rightPaddle.winningScore) winScreen = true;
        ball.speedX = ball.generateRandomSpeed();
        ball.x = maxWidth / 2;
        ball.y = maxHeight / 2;
    }

    function update(delta) {
        if (winScreen) {
            return;
        }

        // move paddles
        mouseMovement(leftPaddle);
        computerMovement(rightPaddle);

        // move ball
        ball.x += ball.speedX * delta;
        ball.y += ball.speedY * delta;

        // handle ball hitting paddles and walls
        if (ball.x - ball.radius < 0) {
            if (ball.y > leftPaddle.y && ball.y < leftPaddle.y + leftPaddle.height) {
                ball.speedX = -ball.speedX;

                let paddleDeltaY = ball.y - leftPaddle.center();
                ball.speedY = paddleDeltaY * leftPaddle.effect;
            } else {
                rightPaddle.score++; // must be BEFORE reset
                ballReset();
            }
        }
        if (ball.x + ball.radius > canvas.width) {
            if (ball.y > rightPaddle.y && ball.y < rightPaddle.y + rightPaddle.height) {
                ball.speedX = -ball.speedX;

                let paddleDeltaY = ball.y - rightPaddle.center();
                ball.speedY = paddleDeltaY * rightPaddle.effect;
            } else {
                leftPaddle.score++; // must be BEFORE reset
                ballReset();
            }
        }

        // Bounce ball from top and bottom of canvas
        if (ball.y - ball.radius <= 0 || ball.y + ball.radius >= maxHeight) {
            ball.speedY = -ball.speedY;
        }
    }

    // draws colored Rectangle
    function colorRect(color, leftX, topY, width, height) {
        ctx.fillStyle = color;
        ctx.fillRect(leftX, topY, width, height);
    }

    // draws colored Circle
    function colorCircle(color, centerX, centerY, radius) {
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.fill();
    }

    function draw() {
        // black everything
        colorRect("black", 0, 0, canvas.width, canvas.height);

        // draws scores
        ctx.fillStyle = "white";
        ctx.fillText(leftPaddle.score, maxWidth / 2 - 40, 30);
        ctx.fillText(rightPaddle.score, maxWidth / 2 + 30, 30);

        // draws net
        for (let i = 0; i < maxHeight; i += 40) colorRect("white", maxWidth / 2 - 1, i, 2, 20);

        // draw winScreen
        if (winScreen) {
            ctx.font = "100px serif";
            if (leftPaddle.score >= leftPaddle.winningScore) {
                ctx.fillText("Left Wins", 40, maxHeight - 40);
            } else if (rightPaddle.score >= rightPaddle.winningScore) {
                ctx.fillText("Right Wins", 40, maxHeight - 40);
            }
            ctx.font = '24px serif';
            return;
        }

        // draws ball
        colorCircle("white", ball.x, ball.y, ball.radius);
        // draws left Paddle
        colorRect("white", leftPaddle.x, leftPaddle.y, leftPaddle.width, leftPaddle.height);
        // draws right Paddle
        colorRect("white", rightPaddle.x, rightPaddle.y, rightPaddle.width, rightPaddle.height);
        // draws fps counter
        ctx.fillText(Math.round(fps), maxWidth - 35, 30);
    }

    function mainLoop(timestamp) {
        // Throttle the frame rate.
        if (timestamp < lastFrameTimeMs + (1000 / maxFPS)) {
            requestAnimationFrame(mainLoop);
            return;
        }
        delta += timestamp - lastFrameTimeMs;
        lastFrameTimeMs = timestamp;

        if (timestamp > lastFpsUpdate + 1000) {
            fps = 0.25 * framesThisSecond + 0.75 * fps;

            lastFpsUpdate = timestamp;
            framesThisSecond = 0;
        }
        framesThisSecond++;

        let numUpdateSteps = 0;
        while (delta >= timestep) {
            update(timestep);
            delta -= timestep;
            if (++numUpdateSteps >= 240) {
                delta = 0;
                break;
            }
        }
        draw();
        requestAnimationFrame(mainLoop);
    }

    requestAnimationFrame(mainLoop);
</script>

</body>

</html>