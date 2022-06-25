module.exports = {
  serverRuntimeConfig: {
    NEXT_PUBLIC_ENTRYPOINT: process.env.NEXT_PUBLIC_ENTRYPOINT || "https://localhost:81",
  },
  swcMinify: true,
};
