class PusherInst {
    static pusher() {
        return new Pusher("8f49b51adc7ccf8e85a4", {
            cluster: "eu",
            forceTLS: true,
        });
    }
}