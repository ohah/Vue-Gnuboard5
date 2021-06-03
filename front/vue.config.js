module.exports = {
  configureWebpack: {
    devServer: {
      headers: { "Access-Control-Allow-Origin": "*" }
    }
  },
  devServer: {
    allowedHosts: [
      'http://localhost',
      'localhost',
      'localhost:8080',
      'http://localhost:8080',
    ],
    headers: { "Access-Control-Allow-Origin": "*" },
    port: 8080, // CHANGE YOUR PORT HERE!
    proxy: {
      "/api": {
        target: "http://localhost/api",
        changeOrigin: true,
        ws: true,
        pathRewrite: {
          "^/api": ""
        }
      },
    },
    inline: true,
    hot: true,
    stats: "minimal",
    contentBase: __dirname,
    overlay: true,
    historyApiFallback: true
  },
  css:{
    extract:false
  },
  outputDir: './dist',
  productionSourceMap: false
};
