var container = [];
var scene = [];
var camera = [];
var renderer = [];
var labelRenderer = [];
var controls = [];
var root = [];

/**
 * Initialise the threejs object
 * @param string id identifier
 * @param string type object type
 * @param string url path to object
 * @param string mtl material file
 * @param string obj object file
 * @param integer width canvas width
 * @param integer height canvas height
 * @param boolean delay flag to pause loading of object
 * @param string loadstring loading UI message
 * @param string resetstring reset UI message
 * @param string infostring information UI message
 */
function threeinit(id, type, url, mtl, obj, width, height, delay, loadstring, resetstring, infostring) {
    if (delay === "") {
        delay = false;
    } else {
        delay = true;
    }
    switch (type) {
        case 'ply':
            initply(id, url, width, height, delay);
            var load = 'initply("' + id + '","' + url + '",' + width + ',' + height + ', false)';
            break;
        case 'pdb':
            initpdb(id, url, width, height, delay);
            load = 'initpdb("' + id + '","' + url + '",' + width + ',' + height + ', false)';
            break;
        default:
            initobj(id, url, mtl, obj, width, height, delay);
            load = 'initobj("' + id + '","' + url + '","' + mtl + '","' + obj + '",' + width + ',' + height + ', false)';
            break;
    }
    // Create ingo area.
    var caption = document.createElement("div");
    caption.setAttribute('id', id + "_threeinfo");
    caption.setAttribute('class', 'threecaption');
    var txt = document.createTextNode(infostring);
    caption.appendChild(txt);
    $(caption).insertAfter('#' + id);
    // Create load button.
    if (delay) {
        var loadbutton = document.createElement("button");
        loadbutton.setAttribute('id', id + "_loadbutton");
        loadbutton.setAttribute('class', 'threebutton');
        loadbutton.setAttribute('type', 'button');
        loadbutton.setAttribute('onclick', load);
        txt = document.createTextNode(loadstring);
        loadbutton.appendChild(txt);
        caption.appendChild(loadbutton);
    }
    // Create reset button.
    var resetbutton = document.createElement("button");
    resetbutton.setAttribute('id', id + "_resetbutton");
    resetbutton.setAttribute('class', 'threebutton');
    resetbutton.setAttribute('type', 'button');
    resetbutton.setAttribute('onclick', "threereset('" + id + "')");
    txt = document.createTextNode(resetstring);
    resetbutton.appendChild(txt);
    caption.appendChild(resetbutton);
}

$(document).ready(function() {
    $.each($('.threeblock'), function(ind) {
        var id = 'three' + parseInt(ind + 1);
        $(this).attr('id', id);
        container[id] = this;
        container[id].setAttribute("style","width:" + $(this).data("width") + "px;height:" + $(this).data("height") + "px;");
        threeinit(id,
            $(this).data("ext"),
            $(this).data("url"),
            $(this).data("mtl"),
            $(this).data("obj"),
            $(this).data("width"),
            $(this).data("height"),
            $(this).data("delay"),
            $(this).data("loadstring"),
            $(this).data("resetstring"),
            $(this).data("infostring")
        )
    });
});

/**
 * Animate the object
 * @param string id object identifier
 */
function threeanimate(id) {
    controls[id].update();
    threerender(id);
    requestAnimationFrame(function() {
        threeanimate(id);
    });
}

/**
 * Render the object
 * @param string id object identifier
 */
function threerender(id) {
    renderer[id].render(scene[id], camera[id]);
    // Render labels if present.
    if (labelRenderer[id] !== undefined) {
        labelRenderer[id].render(scene[id], camera[id]);
    }
}

/**
 * Reset the camera
 * @param string id object identifier
 */
function threereset(id) {
    controls[id].reset();
}

/**
 * Set object controls
 * @param string id object identifier
 */
function threesetcontrols(id) {
    controls[id] = new THREE.TrackballControls(camera[id], renderer[id].domElement);
    controls[id].minDistance = 0;
    controls[id].maxDistance = 2000;
}

/**
 * Set the scene
 * @param string id object identifier
 */
function threeaddscene(id) {
    scene[id] = new THREE.Scene();
    scene[id].background = new THREE.Color(0xffffff);
}