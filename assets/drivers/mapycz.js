
Maps.driver = class {
    static createLayers(map, apiKey) {
         L.tileLayer(`https://api.mapy.com/v1/maptiles/basic/256/{z}/{x}/{y}?apikey=${apiKey}`, {
             minZoom: 0, maxZoom: 19, attribution: '<a href="https://api.mapy.com/copyright" target="_blank">&copy; Seznam.cz a.s. a další</a>',
         }).addTo(map);

    }

    static createCopyright(map) {
        let LogoControl = L.Control.extend({
            options: {
                position: 'bottomleft',
            },

            onAdd: function (map) {
                let container = L.DomUtil.create('div');
                let link = L.DomUtil.create('a', '', container);

                link.setAttribute('href', 'http://mapy.com/');
                link.setAttribute('target', '_blank');
                link.innerHTML = '<img src="https://api.mapy.com/img/api/logo.svg" />';
                L.DomEvent.disableClickPropagation(link);

                return container;
            },
        });

        // finally we add our LogoControl to the map
        new LogoControl().addTo(map);
    }
};

