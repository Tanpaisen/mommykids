/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // ---- MommyKids design tokens ----
        coral: {
          DEFAULT: "#FF6F81", // primary brand
          dark: "#E8536A",    // hover / active
          light: "#FFE3E8",   // tints / badges bg
        },
        peach: {
          DEFAULT: "#FFB199", // secondary / hero gradient
          light: "#FFE9E1",
        },
        mint: {
          DEFAULT: "#3DBE8B", // success / discount / in-stock
          light: "#E4F7EF",
        },
        gold: {
          DEFAULT: "#F5A623", // voucher / promo accent
          light: "#FFF3DC",
        },
        ink: {
          DEFAULT: "#2B2530", // primary text
          soft: "#6B6470",    // secondary text
        },
        cream: "#FFF8F5",     // page background
        surface: "#FFFFFF",   // card background
      },
      fontFamily: {
        display: ["'Baloo 2'", "cursive"],
        body: ["'Be Vietnam Pro'", "sans-serif"],
      },
      borderRadius: {
        blob: "42% 58% 65% 35% / 45% 40% 60% 55%",
        card: "1.25rem",
        pill: "999px",
      },
      boxShadow: {
        soft: "0 6px 20px -6px rgba(43, 37, 48, 0.12)",
        pop: "0 10px 30px -8px rgba(255, 111, 129, 0.35)",
      },
    },
  },
  plugins: [],
};
