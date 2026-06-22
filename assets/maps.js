const Maps = {
    driver: null,
};



Maps.Suggestion;


Maps.BaseMap = class {

    map;
    settings;

    constructor(settings) {
        this.settings = settings;
    }

    createMap(identifier) {
        this.map = L.map(identifier).setView([this.settings.center.latitude, this.settings.center.longitude], this.settings.defaultZoom); // czech
    }

    createLayers() {
        Maps.driver.createLayers(this.map, this.settings.apiKey);

    }
    createCopyright() {
        Maps.driver.createCopyright(this.map);
    }
};


Maps.MapControl = class extends Maps.BaseMap {
    init(identifier) {

        this.createMap(identifier);

        this.createLayers();

        this.createCopyright();

        this.addMarkers();
    }

    addMarkers() {
        let bounds = new L.LatLngBounds([]);
        if (Array.isArray(this.settings.markers) === true && this.settings.markers.length > 0) {
            for (let m of this.settings.markers) {
                const mSettings = JSON.parse(m.toString());
                let coords = L.latLng([mSettings.latitude, mSettings.longitude])
                let options = {};
                if (mSettings.icon) {
                    let icon = L.icon(mSettings.icon)
                    options.icon = icon;
                }
                if (mSettings.divIcon) {
                    let icon = L.divIcon(mSettings.divIcon)
                    options.icon = icon;
                }
                let marker = L.marker(coords, options).addTo(this.map).bindPopup(mSettings.title);
                bounds.extend(coords)
            }
            this.map.fitBounds(bounds, {
                padding: [20, 20], maxZoom: this.settings.maxZoom
            });
        }
    }
};

Maps.MapPicker = class extends Maps.BaseMap {


    marker;

    init(identifier) {

        this.createMap(identifier);

        this.createLayers();

        this.createCopyright();

        this.map.on('click', (e) => {
            let popLocation = e.latlng;
            this.setPosition(popLocation.lat, popLocation.lng);
        });

        let suggestionEl = document.querySelector('#' + identifier + '-autocomplete');
        if (suggestionEl) {
            if (Maps.Suggestion !== undefined) {
                let suggestion = Maps.Factory.createSuggestion(this.settings.suggestionApiKey);
                suggestion.init(document.querySelector('#' + identifier + '-autocomplete'), (event,inputElem) => {
                    let origData = event.detail.selection.value.data;
                    inputElem.value = '';
                    this.setPosition(origData.position.lat, origData.position.lon, true)
                })
            } else {
                suggestionEl.remove();
            }
        }
    }

    setPosition = (lat, lon, zoom = false) => {
        zoom = zoom ? this.settings.defaultZoom : this.map.getZoom();
        if (this.marker) this.map.removeLayer(this.marker);
        this.marker = L.marker([lat, lon]).addTo(this.map);
        this.marker.bindPopup(`Geocoded to ${lat},${lon}`);
        let latLngs = [this.marker.getLatLng()];
        let markerBounds = L.latLngBounds(latLngs);
        this.map.setView([latLngs[0].lat, latLngs[0].lng], zoom);
        document.querySelector("#" + this.settings.inputId).value = `${lat} ${lon}`;
    }


};


Maps.Factory = class {
    /**
     * @param document
     * @param settings
     * @returns {MapPicker}
     */
    static createMapPicker(settings) {
        return new Maps.MapPicker(settings);
    }

    /**
     * @param document
     * @param settings
     * @returns {MapControl}
     */
    static createMapControl(settings) {
        return new Maps.MapControl(settings);
    }

    static createSuggestion(apiKey, placeholder, lang) {
        return new Maps.Suggestion(apiKey, placeholder, lang);
    }
};
