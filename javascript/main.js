$(document).ready(function() {
    const varHolder = $('body').data("varHolder");
});

    class Message {
        #messagetypes = ["log", "info", "error", "debug"];
        #type = "";
        #message = "";
        #timeout = 3000;

        constructor(type) {
            this.#type = type;
            this.#validateMessageTypes(type);
        }

        setMessage(message) {
            this.#message = message;
        }

        setHideTimeout(timeout){
            this.#timeout = timeout;
        }

        showMessage() {
            let message = this.#message;
            const $messagebox = $('#messagebox');
            let color = this.#getBoxColor(this.#type);
            this.#validateMessage(message);

            $messagebox.find('p').text(message);
            $messagebox.removeClass('errorColor logColor infoColor debugColor').addClass(color);

            $messagebox.css({ right: '-300px', opacity: 0 }).show()
                .animate({ right: '0.5%', opacity: 1 }, 500);
            
            setTimeout(() => {
                $messagebox.animate({ right: '-300px', opacity: 0 }, 500, function() {
                    $(this).hide();
                    $(this).removeClass(color);
                    $(this).find('p').text("");
                });
            }, this.#timeout);
        }

        #getBoxColor(type) {
            switch(type) {
                case "error":
                    return "errorColor";

                case "log":
                    return "logColor";

                case "info":
                    return "infoColor";

                case "debug":
                    return "debugColor";

                default:
                    throw new Error("Unknown Message Type!");
            }
        }

        #validateMessageTypes(type) {
            if(!this.#messagetypes.includes(type)) {
                throw new Error("Unknown Message Type!");
            }
        }

        #validateMessage(message) {
            if(!message) {
                throw new Error("You have to set a message!");
            }
        }
    }