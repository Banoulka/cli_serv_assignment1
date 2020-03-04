class DataDisplayer {

    setData(data) {
        this.data = data;
    }

    setParent(parent) {
        this.parent = parent;
    }

    setTemplate(templateString) {
        this.template = templateString;
    }

    display() {
        // For each item in the data array
        this.data.forEach(item => {
            // Copy the htmlstring with the %% template tags
            let htmlString = this.template;

            // Find the occurances of %<something>% in an array
            let occurances = (htmlString.match(/%[a-z_\-]*%/gi) || []);

            // For each of the occurances, remove the %'s and check
            // whether that item exists in the current data object,
            // if it does, replace it with that value, if it does not
            // then dont
            for (let i = 0; i < occurances.length; i++) {
                // remove the %'s
                let tag = occurances[i].replace(/%/g, "");

                // get the tag out of the data
                let data = item[tag];

                // if the data is not empty, replace the occurance with the data
                if (data !== undefined) {
                    htmlString = htmlString.replace(occurances[i], data);
                }
            }

            // Attach the html onto the end of the parent
            this.parent.innerHTML += htmlString;
        });
    }

}