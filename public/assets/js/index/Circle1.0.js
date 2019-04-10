/*
 * CircleJs1.1
 * 2018-11-16 14:52
 * 青橙科技YanEr
 */
function CircleJs(opt) {
    var defaultVal = {
        circles: document.getElementById("circleJsSvg"),
        bg: document.getElementById("circleJsBgSvg"),
        rectCircleNum: "05",
        gId: "circleJsSvg",
        lineCoor: {
            x: 400,
            y: 600
        },
        svgAttr: {
            w: 640,
            h: 640,
            css: "",
            scale: 1
        },
        circleOpt: {
            "circleJsSvg09": {
                animate: {
                    speed: "20s",
                    direction: "0-360"
                }
            },
            "circleJsSvg08": {
                animate: {
                    speed: "2s",
                    direction: "360-0"
                }
            },
            "circleJsSvg06": {
                animate: {
                    speed: "5s",
                    direction: "0-360"
                }
            },
            "circleJsSvg05": {
                animate: {
                    speed: "10s",
                    direction: "360-0"
                }
            },
            "circleJsSvg03": {
                animate: {
                    speed: "5s",
                    direction: "360-0"
                }
            },
            "circleJsSvg02": {
                animate: {
                    speed: "2s",
                    direction: "0-360"
                }
            },
            "circleJsSvg01": {
                animate: {
                    speed: "2s",
                    direction: "360-0",
                    range: 10
                }
            },
            "circleJsSvgRect01": {
                rect: { w: 24, h: 24 },
                animate: {
                    speed: "3s",
                    direction: "0-360"
                }
            },
            "circleJsSvgRect02": {
                rect: { w: 24, h: 24 },
                animate: {
                    speed: "3s",
                    direction: "0-360"
                }
            }
        },
        bgAttr: {
            w: "640",
            h: "1136",
            css: "",
            scale: 1,
            filterId: "soptFilter",
            fill: "rgb(8,209,254)",
            svgColor: "rgb(0,0,0)",
            soptNum: 50,
            soptRMax: 5,
            soptRMin: 0.5,
            soptSpeedMax: 10,
            soptRangeMax: 20,
            circleGId: "soptGroup",
            lineGId: "lineGroup",
            lineCGId: "lineCircleGroup",
            lightGId: "bgLightGroup"
        }
    };

    for (let o in opt) {
        if (defaultVal[o] !== undefined && defaultVal[o] !== null) {
            if (o === "svgAttr" || o === "circleOpt" || o === "bgAttr") {
                for (let o2 in opt[o]) {
                    if (opt[o][o2] instanceof Object) {
                        for (let o3 in opt[o][o2]) {
                            if (o3 === "animate") {
                                for (let o4 in opt[o][o2][o3]) {
                                    defaultVal[o][o2][o3][o4] = opt[o][o2][o3][o4];
                                }
                            } else {
                                defaultVal[o][o2][o3] = opt[o][o2][o3];
                            }
                        }
                    } else {
                        defaultVal[o][o2] = opt[o][o2];
                    }
                }
            } else {
                defaultVal[o] = opt[o];
            }
        }
    }

    for (let i in defaultVal) {
        this[i] = defaultVal[i];
    }

    this.initialC();
}

CircleJs.prototype.initialC = function () {
    var _this = this;
    this.setAttr(_this.circles, {
        width: _this.svgAttr.w,
        height: _this.svgAttr.h,
        style: _this.svgAttr.css,
        transform: "scale(" + _this.svgAttr.scale + ")"
    });
    this.setAttr(_this.bg, {
        width: _this.bgAttr.w,
        height: _this.bgAttr.h,
        style: _this.bgAttr.css,
        transform: "scale(" + _this.bgAttr.scale + ")",
    });
    this.setCircles();
    if (this.circleOpt instanceof Object) {
        this.circleChange();
    }
    this.bgFillandAnimate();
    this.bgSoptCreate();
    this.bgLineInitial(_this.lineCoor.x, _this.lineCoor.y);
};

CircleJs.prototype.setAttr = function (el, attrObj) {
    for (let i in attrObj) {
        el.setAttribute(i, attrObj[i]);
    }
};

CircleJs.prototype.find = function (el, val, callback) {
    if (el && val) {
        var elChild = el.children;
        var resList = [];
        var findAttr = "nodeName";
        if (/\#/g.test(val)) {
            findAttr = "id";
            val = val.replace("#", "");
        }
        if (/\./g.test(val)) {
            findAttr = "className";
            val = val.replace(".", "");
        }
        if (elChild.length > 0) {
            for (let r in elChild) {
                if (typeof elChild[r] === "object") {
                    var haveVal = elChild[r][findAttr].search(val);
                    if (haveVal === 0) {
                        resList.push(elChild[r]);
                        if (callback) {
                            callback(elChild[r]);
                        }
                    }
                }
            }
        }
        return resList;
    }
};

CircleJs.prototype.random = function (min, max) {
    return min + Math.random() * (max - min);
};

CircleJs.prototype.setCircles = function () {
    var _this = this;
    this.find(this.circles, "#" + this.gId, function (el) {
        _this.find(el, "circle", function (cEl) {
            _this.setAttr(cEl, {
                cx: _this.svgAttr.w / 2,
                cy: _this.svgAttr.w / 2
            });
        });
    });
};

CircleJs.prototype.circleChange = function () {
    var _this = this;
    var cOpt = this.circleOpt;

    for (let cId in cOpt) {
        var addAttr = {};
        var el = document.getElementById(cId);
        var elOpt = cOpt[cId];
        if (elOpt.r || typeof elOpt.r === "number") {
            addAttr.r = elOpt.r;
        }
        if (elOpt.style || typeof elOpt.style === "string") {
            addAttr.style = elOpt.style;
        }
        if (elOpt.border) {
            var border = elOpt.border;
            if (border instanceof Array && border.length > 0) {
                addAttr["stroke-width"] = border[0];
                if (border.length > 1) {
                    addAttr.stroke = border[1];
                }
            }
        }
        if (elOpt.fill) {
            var fill = elOpt.fill;
            if (typeof fill === "string") {
                addAttr.fill = fill;
            } else if (fill instanceof Object) {
                console.error("填充必须为颜色值，且类型应为字符串");
            }
        }

        _this.find(el, "circle", function (cEl) {
            _this.setAttr(cEl, addAttr);
        });

        if (elOpt.rect instanceof Object && elOpt.rect !== null) {
            var rect = elOpt.rect;
            _this.rectChange(cId, rect);
        }

        if (elOpt.animate || elOpt.animate instanceof Object) {
            _this.animateChange(el, elOpt.animate);
        }
    }
};

CircleJs.prototype.rectChange = function (cId, rect) {
    var _this = this;
    var el = document.getElementById(cId);
    var circleGEl = document.getElementById(_this.gId + _this.rectCircleNum);
    _this.find(circleGEl, "circle", function (circleEl) {
        var circleR = parseInt(circleEl.getAttribute("r"));
        if (cId === (_this.gId + "Rect01")) {
            let flag = 0;
            _this.find(el, "rect", function (rectEl) {
                if (flag === 0) {
                    _this.setAttr(rectEl, {
                        width: rect.w,
                        height: rect.h,
                        x: _this.svgAttr.w / 2 - circleR - rect.w / 2,
                        y: _this.svgAttr.h / 2 - rect.h / 2
                    });
                }
                if (flag === 1) {
                    _this.setAttr(rectEl, {
                        width: rect.w,
                        height: rect.h,
                        x: _this.svgAttr.w / 2 + circleR - rect.w / 2,
                        y: _this.svgAttr.h / 2 - rect.h / 2
                    });
                }
                flag++;
            });
        }
        if (cId === (_this.gId + "Rect02")) {
            let flag = 0;
            _this.find(el, "rect", function (rectEl) {
                if (flag === 0) {
                    _this.setAttr(rectEl, {
                        width: rect.w,
                        height: rect.h,
                        x: _this.svgAttr.w / 2 - rect.h / 2,
                        y: _this.svgAttr.h / 2 - circleR - rect.h / 2
                    });
                }
                if (flag === 1) {
                    _this.setAttr(rectEl, {
                        width: rect.w,
                        height: rect.h,
                        x: _this.svgAttr.w / 2 - rect.h / 2,
                        y: _this.svgAttr.h / 2 + circleR - rect.h / 2
                    });
                }
                flag++;
            });
        }
    });
};

CircleJs.prototype.animateChange = function (el, animateOpt, isBg) {
    var _this = this;
    animateOpt.direction = animateOpt.direction || "0-360";
    function setAnimateTransformAttr(argEl, isAdd) {
        var direction = animateOpt.direction.split("-");
        var elW = _this.svgAttr.w;
        var elH = _this.svgAttr.h;
        var setTo = direction[1] + " " + elW / 2 + " " + elH / 2;
        var setFrom = direction[0] + " " + elW / 2 + " " + elH / 2;
        if (isBg) {
            setFrom = animateOpt.from;
            setTo = animateOpt.to;
        }
        if (animateOpt.range) {
            setTo = direction[1] + " " + (elW / 2 - animateOpt.range) + " " + (elH / 2 - animateOpt.range);
        }
        if (isAdd) {
            _this.setAttr(argEl, {
                attributeName: "transform",
                begin: "0s",
                type: "rotate",
                repeatCount: "indefinite",
                dur: animateOpt.speed,
                from: setFrom,
                to: setTo
            });
        } else {
            _this.setAttr(argEl, {
                dur: animateOpt.speed,
                from: setFrom,
                to: setTo
            });
        }
    }
    var isFindEl = this.find(el, "animateTransform", function (animateEl) {
        setAnimateTransformAttr(animateEl);
    });
    if (isFindEl.length === 0) {
        var aEl = document.createElementNS('http://www.w3.org/2000/svg', "animateTransform");
        setAnimateTransformAttr(aEl, true);
        el.appendChild(aEl);
    }
};

CircleJs.prototype.bgSoptCreate = function () {
    var _this = this;
    var cAttr = _this.bgAttr;
    for (let s = 0; s < _this.bgAttr.soptNum; s++) {
        let r = _this.random(cAttr.soptRMin, cAttr.soptRMax);
        let x = _this.random(r, _this.bgAttr.w);
        let y = _this.random(r, _this.bgAttr.h);
        let animate = {
            speed: _this.random(2, cAttr.soptSpeedMax) + "s",
            from: "0 " + x + " " + y,
            to: "360 " + (x - _this.random(0, _this.bgAttr.soptRangeMax)) + " " + (y - _this.random(0, _this.bgAttr.soptRangeMax))
        };
        _this.bgChange("add", x, y, r, animate);

    }
};

CircleJs.prototype.bgFillandAnimate = function () {
    var _this = this;
    var fill = _this.bgAttr.fill.replace("rgb(", "").replace(")", "");
    var direction = [[0, 360], [360, 0]];
    _this.find(_this.bg, "#" + _this.bgAttr.circleGId, function (gEl) {
        _this.find(gEl, "circle", function (cEl) {
            _this.setAttr(cEl, {
                fill: "rgba(" + fill + "," + Math.random() + ")"
            });
            var d = direction[Math.round(Math.random())];
            let x = cEl.getAttribute("cx");
            let y = cEl.getAttribute("cy");
            let animate = {
                speed: _this.random(2, _this.bgAttr.soptSpeedMax) + "s",
                from: d[0] + " " + x + " " + y,
                to: d[1] + " " + (x - _this.random(5, 20)) + " " + (y - _this.random(5, 20))
            };
            _this.animateChange(cEl, animate, true);
        });
    });
};

CircleJs.prototype.bgChange = function (operation, x, y, r, animate) {
    var _this = this;
    var bg = this.bg;
    function getAttrInt(el, attrName) {
        let a = el.getAttribute(attrName);
        return parseInt(a);
    }
    if (operation === "add") {
        var soptNode = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        var fill = _this.bgAttr.fill.replace("rgb(", "").replace(")", "");
        _this.setAttr(soptNode, {
            cx: x,
            cy: y,
            r: r,
            fill: "rgba(" + fill + "," + Math.random() + ")"
        });
        this.find(bg, "#" + _this.bgAttr.circleGId, function (gEl) {
            gEl.appendChild(soptNode);
            if (animate) {
                _this.animateChange(soptNode, animate, true);
            }
        });
    }
    if (operation === "del") {
        _this.find(bg, "#" + _this.bgAttr.circleGId, function (gEl) {
            _this.find(gEl, "circle", function (cEl) {
                if (cEl) {
                    var isX = getAttrInt(cEl, "cx") === x;
                    var isY = getAttrInt(cEl, "cy") === y;
                    var isR = getAttrInt(cEl, "r") === r;
                    if (isX && isY && isR) {
                        gEl.removeChild(cEl);
                    }
                }

            });
        });
    }
    if (operation === "change" && x instanceof Array && y instanceof Array) {
        if (x.length >= 3 && y.length >= 3) {
            _this.find(bg, "#" + _this.bgAttr.circleGId, function (gEl) {
                _this.find(gEl, "circle", function (cEl) {
                    var isX = getAttrInt(cEl, "cx") === x[0];
                    var isY = getAttrInt(cEl, "cy") === x[1];
                    var isR = getAttrInt(cEl, "r") === x[2];
                    if (isX && isY && isR) {
                        _this.setAttr(cEl, {
                            cx: y[0],
                            cy: y[1],
                            r: y[2]
                        });
                    }
                });
            });
        }
    }
};

CircleJs.prototype.bgLineCreate = function (start, end) {
    var _this = this;
    var lId = _this.bgAttr.lineGId;
    var cId = _this.bgAttr.lineCGId;
    var lEl = document.getElementById(lId);
    var cEl = document.getElementById(cId);
    var direction = [[0, 360], [360, 0]];

    for (let i = 0; i < end.length; i++) {
        var lineNode = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        _this.setAttr(lineNode, {
            x1: start[0],
            y1: start[1],
            x2: end[i][0],
            y2: end[i][1]
        });
        lEl.appendChild(lineNode);
    }

    var fill = _this.bgAttr.fill.replace("rgb(", "").replace(")", "");
    var lineCircleNode = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    _this.setAttr(lineCircleNode, {
        cx: start[0],
        cy: start[1],
        r: _this.random(3, 8),
        fill: "rgba(" + fill + "," + _this.random(0.5, 1) + ")"
    });
    lEl.appendChild(lineCircleNode);
    let d = direction[Math.round(Math.random())];
    let animate = {
        speed: _this.random(2, _this.bgAttr.soptSpeedMax) + "s",
        from: d[0] + " " + start[0] + " " + start[1],
        to: d[1] + " " + (start[0] - _this.random(1, 3)) + " " + (start[1] - _this.random(1, 3))
    };
    _this.animateChange(lineCircleNode, animate, true);
};

CircleJs.prototype.bgLineInitial = function (x, y) {
    var _this = this;

    var a = [x + 120, y]; 
    var b = [x + 140, y + 150]; 
    var c = [x + 30, y + 80]; 
    var d = [x + 105, y + 160]; 
    var e = [x, y + 150]; 
    var f = [x + 128, y + 190]; 
    var g = [x + 50, y + 220]; 
    var h = [x + 100, y + 220]; 

    this.bgLineCreate([x, y], [a, b, c]);
    this.bgLineCreate(a, [b, d, e, c]);
    this.bgLineCreate(c, [b, f, g]);
    this.bgLineCreate(e, [d, f, g]);
    this.bgLineCreate(b, [d, f]);
    this.bgLineCreate(f, [h]);
    this.bgLineCreate(h, [g]);

    var gEl = document.getElementById(_this.bgAttr.lineGId);
    var animateNode = document.createElementNS("http://www.w3.org/2000/svg", "animateMotion");
    this.setAttr(animateNode, {
        path: "M5,200 A80,200 -45 0,1 50 5 A80,200 -45 0,1 5,160",
        dur: "15s",
        repeatCount: "indefinite"
    });
    gEl.appendChild(animateNode);
};

CircleJs.prototype.pause = function () {
    this.circles.pauseAnimations();
};

CircleJs.prototype.unpause = function () {
    this.circles.unpauseAnimations();
};