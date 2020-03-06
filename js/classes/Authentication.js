class Authentication {


    static async getUser() {
        let user = JSON.parse(sessionStorage.getItem("user"));

        if (!user) {
            const ures = await fetch("../requests/auth/me");
            const ujson = await ures.json();

            sessionStorage.setItem("user", JSON.stringify(ujson.user));
            user = ujson.user;
        }
        return user;
    }
}