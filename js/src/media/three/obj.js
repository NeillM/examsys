/**
 * Set up obj (with mtl) object
 * @param string id object identifier
 * @param string file the archive file
 * @param string obj the object file
 * @param string mtl the material file
 * @param integer width width of renderer
 * @param integer height height of renderer
 * @param boolean delay flag to stop setup
 */
function initobj(id, file, mtl, obj, width, height, delay) {
    if (delay !== true) {
        threeaddscene(id);

        camera[id] = new THREE.PerspectiveCamera(70, width / height, 1, 5000);
        camera[id].position.z = 250;
        scene[id].add(camera[id]);

        var light = new THREE.AmbientLight(0xffffff, 0.8);
        scene[id].add(light);

        renderer[id] = new THREE.WebGLRenderer({antialias: true});
        renderer[id].setPixelRatio(window.devicePixelRatio);
        renderer[id].setSize(width, height);
        container[id].appendChild(renderer[id].domElement);

        threesetcontrols(id);
        loadobj(id, file, mtl, obj);
        threeanimate(id);
    }
}

/**
 * Load the obj file
 * @param string id object identifier
 * @param string file the object file
 * @param string mtl the material file
 * @param string obj the object file
 */
function loadobj(id, file, mtl, obj) {
    // Only obj file supplied so display skeleton.
    if (mtl === "" && obj === "") {
        var loader = new THREE.OBJLoader();
        loader.load(file, function (object) {
            object.position.y = -95;
            scene[id].add(object);
            threerender(id);
        });
    // Archive include obj file and materials.
    } else {
        file = file.substr(file.indexOf('filename=') + 9);
        var dir = file.replace('.zip', '');
        THREE.Loader.Handlers.add(/\.dds$/i, new THREE.DDSLoader());
        loader = new THREE.MTLLoader();
        loader.setPath('/getfile.php?type=media&filename=' + dir + '/');
        loader.load(mtl, function (materials) {
            materials.preload();
            var loader2 = new THREE.OBJLoader();
            loader2.setMaterials(materials);
            loader2.setPath('/getfile.php?type=media&filename=' + dir + '/');
            loader2.load(obj, function (object) {
                object.position.y = -95;
                scene[id].add(object);
            });
            threerender(id);
        });
    }
}
