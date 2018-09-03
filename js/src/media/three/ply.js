/**
 * Set up ply object
 * @param string id object identifier
 * @param string file the object file
 * @param integer width width of renderer
 * @param integer height height of renderer
 * @param boolean delay flag to stop setup
 */
function initply(id, file, width, height, delay) {
    if (delay !== true) {
        threeaddscene(id);

        camera[id] = new THREE.PerspectiveCamera(70, width / height, 1, 5000);
        camera[id].position.z = 10;
        scene[id].add(camera[id]);

        var light = new THREE.HemisphereLight( 0x000000, 0xffffff )
        scene[id].add(light);

        addShadowedLight(id, 1, 1, 1, 0xffffff, 1.35 );
        addShadowedLight(id, 0.5, 1, -1, 0xffffff, 1 );

        renderer[id] = new THREE.WebGLRenderer({antialias: true});
        renderer[id].setPixelRatio(window.devicePixelRatio);
        renderer[id].setSize(width, height);
        renderer[id].gammaInput = true;
        renderer[id].gammaOutput = true;

        renderer[id].shadowMap.enabled = true;
        container[id].appendChild(renderer[id].domElement);

        threesetcontrols(id);
        loadply(id, file);
        threeanimate(id);
    }
}

/**
 * Load the ply file
 * @param string id object identifier
 * @param string file the object file
 */
function loadply(id, file) {
    var loader = new THREE.PLYLoader();
    loader.load(file, function (geometry) {
        var material = new THREE.MeshStandardMaterial({vertexColors: THREE.VertexColors, flatShading: true});
        var mesh = new THREE.Mesh(geometry, material);
        mesh.position.y = 0;
        mesh.position.z = 0;
        mesh.scale.multiplyScalar(0.01);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        scene[id].add(mesh);
        threerender(id);
    });
}

/**
 * Add lighting affects
 * @param string id object identifier
 * @param float x source x-coord
 * @param float y source y-coord
 * @param float z source z-coord
 * @param hex color colour of light
 * @param float intensity intensity of light
 */
function addShadowedLight(id, x, y, z, color, intensity ) {

    var directionalLight = new THREE.DirectionalLight( color, intensity );
    directionalLight.position.set( x, y, z );
    scene[id].add( directionalLight );

    directionalLight.castShadow = true;

    var d = 1;
    directionalLight.shadow.camera.left = -d;
    directionalLight.shadow.camera.right = d;
    directionalLight.shadow.camera.top = d;
    directionalLight.shadow.camera.bottom = -d;

    directionalLight.shadow.camera.near = 1;
    directionalLight.shadow.camera.far = 4;

    directionalLight.shadow.mapSize.width = 1024;
    directionalLight.shadow.mapSize.height = 1024;

    directionalLight.shadow.bias = -0.001;

}