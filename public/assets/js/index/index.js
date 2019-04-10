// ----- 动画调用及配置 -----

window.onload = function () {
    var screenW = window.innerWidth;
    var screenH = window.innerHeight;
    var scale = screenW / 640;
    var svgTop = "-100";
    if (screenW >= 640) {
        scale = 1;
        svgTop = 0;
    }

    var cj = new CircleJs({
        svgAttr: {
            scale: scale,
            css: "-webkit-transform:scale(" + scale + "); top: " + svgTop + "px;"
        },
        bgAttr: {
            w: screenW,
            h: screenH
        },
        lineCoor: {
            x: screenW * 0.5,
            y: screenH * 0.5
        }
    });
};