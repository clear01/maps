Maps.Suggestion = class {
    queryCache = {}
    apiKey;
    noresult;
    lang;
    placeholder;

    constructor(apiKey, placeholder, noresult, lang) {
        this.noresult = noresult ?? "Žádné výsledky pro dotaz";
        this.lang = lang ?? 'cs';
        this.placeholder = placeholder ?? "Zadejte adresu pro vyhledávání";
        this.apiKey = apiKey;
    }
    init(inputElem, onSelect, types = ['regional.municipality', 'regional.municipality_part', 'regional.street', 'regional.address', 'coordinate']) {
        // get items by query
        let autoCompleteJS = new autoComplete({
            selector: () => inputElem, placeHolder: this.placeholder, searchEngine: (query, record) => `<mark>${record}</mark>`, data: {
                keys: ["value"], src: async (query) => {
                    // get items for current query
                    let items = await this.getItems(query, types);

                    // cache hit? - there is a problem, because this provider needs to get items
                    // for each query and cannot handle different timeouts for different query.
                    // if previous query was completed - it's already in the cache, and some
                    // old query is completed, we test it againts current query and returns correct items.
                    if (this.queryCache[inputElem.value]) {
                        return this.queryCache[inputElem.value];
                    }

                    return items;
                }, cache: false,
            }, resultItem: {
                element: (item, data) => {
                    let itemData = data.value.data;
                    let desc = document.createElement("div");

                    desc.style = "overflow: hidden; white-space: nowrap; text-overflow: ellipsis;";
                    desc.innerHTML = (types.length > 1 ? `${itemData.label}, ` : ``) + `${itemData.location}`;
                    item.append(desc,);
                }, highlight: true
            }, resultsList: {
                element: (list, data) => {
                    list.style.maxHeight = "max-content";
                    list.style.overflow = "hidden";

                    if (!data.results.length) {
                        let message = document.createElement("div");

                        message.setAttribute("class", "no_result");
                        message.style = "padding: 5px";
                        message.innerHTML = `<span>${this.noresult} "${data.query}"</span>`;
                        list.prepend(message);
                    } else {
                        let logoHolder = document.createElement("div");
                        let text = document.createElement("span");
                        let img = new Image();

                        logoHolder.style = "padding: 5px; display: flex; align-items: center; justify-content: end; gap: 5px; font-size: 12px;";
                        text.textContent = "Powered by";
                        img.src = "https://api.mapy.cz/img/api/logo-small.svg";
                        img.style = "width: 60px";
                        logoHolder.append(text, img);
                        list.append(logoHolder);
                    }
                }, noResults: true,
            },
        });
        inputElem.addEventListener("selection", (event) => onSelect(event, inputElem));
    }
    getItems = async (query, types) => {
        if (this.queryCache[query]) {
            return this.queryCache[query];
        }

        try {
            let url = new URL(`https://api.mapy.cz/v1/suggest`);

            url.searchParams.set('lang', this.lang);
            url.searchParams.set('apikey', this.apiKey);
            url.searchParams.set('query', query);
            url.searchParams.set('limit', '5');
            types.forEach(type => url.searchParams.append('type', type));

            let fetchData = await fetch(url.toString(), {});
            let jsonData = await fetchData.json();
            // map values to { value, data }
            let items = jsonData.items.map(item => ({
                value: item.name, data: item,
            }));

            // save to cache
            this.queryCache[query] = items;

            return items;
        } catch (exc) {
            return [];
        }
    };
}