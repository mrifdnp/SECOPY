import Alpine from "alpinejs";
import { getCookie } from "../helper";

export default function apiStore() {
    Alpine.store("api", {
        loading: false,
        error: null,

        async request(method, url, body = null) {
            this.loading = true;
            this.error = null;

            let options = {
                method: method,
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            };

            let csrfToken = getCookie("XSRF-TOKEN");

            if (csrfToken) {
                options.headers["X-XSRF-TOKEN"] = decodeURIComponent(csrfToken);
            }

            options.credentials = "same-origin";

            if (body && method !== "GET") {
                options.body = JSON.stringify(body);
            }

            try {
                let res = await fetch(url, options);
                
                let responseData;
                try {
                    responseData = await res.json();
                } catch (parseError) {
                    responseData = null;
                }

                if (!res.ok) {
                    const error = new Error(`HTTP ${res.status}: ${res.statusText}`);
                    error.response = {
                        status: res.status,
                        statusText: res.statusText,
                        data: responseData
                    };
                    throw error;
                }
                return responseData;
                
            } catch (e) {
                this.error = e.message;
                console.error('API Request Error:', e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async get(url, config = {}) {
            let params = config.params || config;
            if (params) {
                const query = new URLSearchParams(params).toString();
                url += (url.includes("?") ? "&" : "?") + query;
            }
            return await this.request("GET", url);
        },

        async post(url, body) {
            return await this.request("POST", url, body);
        },
        
        async put(url, body) {
            return await this.request("PUT", url, body);
        },
        
        async del(url) {
            return await this.request("DELETE", url);
        },
    });
}