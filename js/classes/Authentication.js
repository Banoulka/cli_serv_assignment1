class Authentication {

    static #user = null;

    static async getUser() {
        if (!this.#user) {
            const ures = await fetch("../requests/auth/me");
            const ujson = await ures.json();
            this.#user = ujson.user;
        }

        return this.#user;
    }
}