# Vue-Gnuboard5

그누보드를 프론트 앤드와 백앤드로 구분하고, 프론트앤드는 VUE로 포팅한 버전입니다.
사용 그누보드버전은 5.4.4.5이며 현재 기본주소(1) 에만 대응합니다.

## 설치


해당 소스를 빌드하는 컴퓨터(시스템)에 node 및 Vue Cli의 최신버전이 설치 되어있어야 합니다.

vue3입니다.

```
git pull https://github.com/ohah/vue-gnuboard5

api폴더는 그누보드가 설치되어있는 폴더에 업로드하세요.

```

### 개발하기
```
git pull https://github.com/ohah/vue-gnuboard5
npm install
npm run serve
```

### 빌드하기
```
npm run build
dist 폴더에 빌드된 파일을 루트 폴더에 업로드

아파치 서버 설정

<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>

Ngnix 서버 설정

location / {
  try_files $uri $uri/ /index.html;
}
```

### 에러 픽스
```
npm run lint
```


### USE Plugin
google/apiclient

### LICENSE

MIT License
Copyright (c) <2020> copyright Vorfeed

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Demo
``` 
http://vuegnu.dothome.co.kr
데모사이트는 일부기능을 이용할 수 없습니다.
```