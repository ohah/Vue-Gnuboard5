module.exports = {
  devServer: {
    port: 8080, // CHANGE YOUR PORT HERE!
    proxy: {
      "/api": {
        //target: 'http://193.123.246.157/blog',
        target: "http://localhost/api",
        changeOrigin: true,
        pathRewrite: {
          "^/api": ""
        }
      }
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
