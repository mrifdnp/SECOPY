import "./bootstrap";
import Swiper from "swiper";
import "iconify-icon";
import Alpine from "alpinejs";
import { Pagination, Navigation } from "swiper/modules";
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import apiStore from "./stores/api";

window.Alpine = Alpine;

apiStore();
Alpine.start();

new Swiper(".swiper", {
    modules: [Navigation, Pagination],
    slidesPerView: "auto",
    centeredSlides: true,
    spaceBetween: 20,
    loop: true,
    autoplay: {
        delay: 5000,
    },
    navigation: {
        nextEl: "#custom-swiper-next",
        prevEl: "#custom-swiper-prev",
    },

    pagination: {
        el: "#swiper-pagination",
        type: "bullets",
    },
});

function initNav() {
    const secondNav = document.getElementById("secondNav");
    const mainNav = document.getElementById("mainNav");
    const content = document.getElementById("main");

    if (!mainNav || !secondNav || !content) {
        console.error("Element tidak ditemukan");
        return;
    }

    let lastScrollY = window.scrollY;
    let lastState = null;
    let ticking = false;
    let isNavHidden = false;

    function updateNav(scrollY) {
        const goingDown = scrollY > lastScrollY;
        const goingUp = scrollY < lastScrollY;

        if (scrollY > 60 && lastState !== true) {
            mainNav.classList.add("fixed","top-0","left-0","w-full","shadow-md","z-50");
            secondNav.classList.add("opacity-0","h-0","py-0","overflow-hidden");
            secondNav.classList.remove("h-[32px]","py-[6px]");
            mainNav.classList.remove("relative","md:block");
            content.style.paddingTop = `${mainNav.offsetHeight + secondNav.offsetHeight}px`;
            lastState = true;
        } else if (scrollY < 20 && lastState !== false) {
            mainNav.classList.remove("fixed","top-0","left-0","w-full","shadow-md","z-50");
            secondNav.classList.remove("opacity-0","h-0","py-0","overflow-hidden");
            secondNav.classList.add("h-[32px]","py-[6px]");
            mainNav.classList.add("relative","md:block");
            content.style.paddingTop = "0px";
            lastState = false;
        }

        if (goingDown && !isNavHidden && scrollY > 100) {
            mainNav.classList.add("translate-y-[-100%]","transition-transform","duration-300","ease-in-out");
            isNavHidden = true;
        }

        if (goingUp && isNavHidden) {
            mainNav.classList.remove("translate-y-[-100%]");
            isNavHidden = false;
        }

        lastScrollY = scrollY;
    }

    window.addEventListener("scroll", () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateNav(window.scrollY);
                ticking = false;
            });
            ticking = true;
        }
    });

    updateNav(window.scrollY);
}

document.addEventListener("DOMContentLoaded", initNav);