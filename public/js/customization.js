var tshirtSource = '';
var designSource = '';

document.querySelector('#customizationStart').addEventListener('click', function(){
    document.querySelector('.imageSelections').classList.add('is-hidden');
    document.querySelector('.designSelection').classList.add('is-hidden');
    document.querySelector('.designArea').classList.remove('is-hidden');
});

function setActiveImage(imageId, src, color) {
    tshirtSource = src;
    tshirtColor = color;
    var images = document.querySelectorAll('.imageSelector');
    for (var i = 1; i <= images.length; i++) {
        var thisImage = document.querySelector('#a' + CSS.escape(i));
        if(imageId === i) {
            thisImage.classList.add('is-activeImage');
            document.querySelector('.designSelection').classList.remove('is-hidden');
        } else {
            thisImage.classList.remove('is-activeImage');
        }
    }
}

function setActiveDesign(designId, src) {
    designSource = src;
    var designs = document.querySelectorAll('.designSelector');
    for (var i = 1; i <= designs.length; i++) {
        var thisDesign = document.querySelector('#b' + CSS.escape(i));
        if(designId === i) {
            thisDesign.classList.add('is-activeDesign');
            document.querySelector('#customizationStart').disabled = false;
        } else {
            thisDesign.classList.remove('is-activeDesign');
        }
    }
}

document.querySelector('#customizationStart').addEventListener('click', function(){
    var baseWidth = 0;
    var baseHeight = 0;
    var customWidth = 0;
    var customHeight = 0;
    var stage;

    function addLines(group, p1, p2, p3, p4, name) {
        var helpLine = new Konva.Line({
            points: [p1, p2, p3, p4],
            name: name,
            stroke: 'blue',
            dash: [1,5],
            strokeWidth: 1,
            id: name
        });
        group.add(helpLine);
    }

    function setLineHit(lines){
        lines.attrs.stroke = 'blue';
        lines.attrs.dash = [1,2];
    }

    function setLineMiss(lines){
        lines.attrs.stroke = 'red';
        lines.attrs.dash = [1,5];
    }

    function setLineColor(img) {
        var width = img.getWidth() * img.getScaleX();
        var height = img.getHeight() * img.getScaleY();
        var horizontalCenter = parseInt(img.getX() + (width / 2));
        var verticalCenter = parseInt(img.getY() + (height / 2));

        var lines;
        lines = stage.find('#vCenter')[0];
        horizontalCenter == parseInt(baseWidth / 2) ? setLineHit(lines) : setLineMiss(lines);

        lines = stage.find('#vLeft')[0];
        horizontalCenter == parseInt(baseWidth / 3) ? setLineHit(lines) : setLineMiss(lines);

        lines = stage.find('#vRight')[0];
        horizontalCenter == parseInt(baseWidth - (baseWidth / 3)) ? setLineHit(lines) : setLineMiss(lines);

        lines = stage.find('#hCenter')[0];
        verticalCenter == parseInt(baseHeight / 2) ? setLineHit(lines) : setLineMiss(lines);

        lines = stage.find('#hTop')[0];
        verticalCenter == parseInt(baseHeight / 3) ? setLineHit(lines) : setLineMiss(lines);

        lines = stage.find('#hBottom')[0];
        verticalCenter == parseInt(baseHeight - (baseHeight / 3)) ? setLineHit(lines) : setLineMiss(lines);
    }

    function drawImage() {
        stage = new Konva.Stage({
            container: 'customizationContainer',
            width: baseWidth,
            height: baseHeight
        });

        var layer = new Konva.Layer();

        var baseImage = new Konva.Image({
            image: baseObj,
            width: baseWidth,
            height: baseHeight
        });
        layer.add(baseImage);

        var customImage = new Konva.Image({
            image: customObj,
            width: customWidth,
            height: customHeight,
            x: (baseWidth - customWidth) / 2,
            y: (baseHeight - customHeight) / 3,
            name: 'customization',
            draggable: true,
            visible: true
        });

        // add cursor styling
        customImage.on('mouseover', function() {
            document.body.style.cursor = 'pointer';
        });
        customImage.on('mouseout', function() {
            document.body.style.cursor = 'default';
        });

        customImage.on('dragmove', function() {
            setLineColor(this);
        });

        baseGroup = new Konva.Group({
            draggable: false
        });

        linesGroup = new Konva.Group({
        });

        stage.add(layer);
        var ctx = baseImage.getContext('2d');
        var x = ctx.getImageData(250, 250, 1, 1).data; // this gets the color of the tshirt. future use: to set the color of the helplines to the inverse of the tshirt color

        addLines(linesGroup, (baseWidth / 2), 0, (baseWidth / 2), baseHeight, 'vCenter');
        addLines(linesGroup, (baseWidth / 3), 0, (baseWidth / 3), baseHeight, 'vLeft');
        addLines(linesGroup, baseWidth - (baseWidth / 3), 0, baseWidth - (baseWidth / 3), baseHeight, 'vRight');
        addLines(linesGroup, 0, (baseHeight / 2), baseWidth, (baseHeight / 2), 'hCenter');
        addLines(linesGroup, 0, (baseHeight / 3), baseWidth, (baseHeight / 3), 'hTop');
        addLines(linesGroup, 0, baseHeight - (baseHeight / 3), baseWidth, baseHeight - (baseHeight / 3), 'hBottom');

        baseGroup.add(customImage);
        baseGroup.add(linesGroup);
        layer.add(baseGroup);
        stage.add(layer);

        document.querySelector('#saveImage').addEventListener('click', function(e){
            linesGroup.destroy();
            stage.find('Transformer').destroy();
            layer.draw();
            var customName = document.querySelector('#customName').value;
            var returnUrl = this.getAttribute("returnHref");
            var destination = this.getAttribute("href");
            var dataURL = stage.toDataURL();

            e.preventDefault();
            $.ajax({
                type: "POST",
                url: destination,
                dataType: "json",
                data: {
                    imgBase64: dataURL,
                    imgName: customName,
                    baseColor: tshirtColor
                }
            }).done(function(o) {
                console.log('saved');
                var x = 0;
                window.location.href = returnUrl;
                // Do here whatever you want.
            });
        });

        customizationContainer.tabIndex = 1;
        customizationContainer.focus();
        customizationContainer.addEventListener('keydown', function(e) {
            var xPosition = customImage.getX();
            var yPosition = customImage.getY();
            var value = e.keyCode;
            if (value === 38) {
                customImage.setY(yPosition - 1);
            } else if (value === 40) {
                customImage.setY(yPosition + 1);
            } else if (value === 37) {
                customImage.setX(xPosition - 1);
            } else if (value === 39) {
                customImage.setX(xPosition + 1);
            } else {
                return;
            }
            setLineColor(customImage);
            layer.draw();
        });

        stage.on('click tap', function (e) {
            // if click on empty area - remove all transformers
            if (e.target === stage) {
                stage.find('Transformer').destroy();
                layer.draw();
                return;
            }
            // do nothing if clicked outside of customImage
            if (!e.target.hasName('customization')) {
                stage.find('Transformer').destroy();
                layer.draw();
                return;
            }

            var tr = new Konva.Transformer({
                centeredScaling: true,
                rotateEnabled: true,
                anchorStroke: "red",
                rotationSnaps: [0, 45, 90, 135, 180, 225, 270, 315, 360]
            });
            layer.add(tr);
            tr.attachTo(e.target);
            layer.draw();
        });
    }

    var baseObj = new Image();
    baseObj.src = tshirtSource;
    baseObj.onload = function() {
        // this.height = 646;
        // this.width = 580;
        this.height = 1292;
        this.width = 1160;
    };

    var customObj = new Image();
    customObj.src = designSource;
    customObj.onload = function() {
        ratio = this.width / this.height
        customHeight = this.height;
        customWidth = customHeight * ratio;
        baseHeight = baseObj.height;
        baseWidth = baseObj.width;
        if (customWidth > (baseWidth * .75)) {
            var scale = customWidth / (baseWidth * .6);
            customWidth = customObj.width / scale;
            customHeight = customObj.height / scale;
        }
        drawImage();
    };
});

