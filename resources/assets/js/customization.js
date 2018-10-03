var baseWidth = 0;
var baseHeight = 0;
var customWidth = 0;
var customHeight = 0;
var stage;
var lineCounter;

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
        container: 'container',
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

    document.querySelector('#saveImage').addEventListener('click', function(){
        linesGroup.destroy();
        layer.draw();
    });

    container.tabIndex = 1;
    container.focus();
    container.addEventListener('keydown', function(e) {
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
baseObj.src = 'images/tshirt-white.png';
baseObj.onload = function() {
    layer.draw();
};

var customObj = new Image();
customObj.src = 'images/print.png';
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
    drawImage(this);
};
