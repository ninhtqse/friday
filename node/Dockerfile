FROM node:latest

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY package*.json /usr/src/app/

RUN npm install

RUN npm install react-scripts@3.0.1 -g 

RUN npm install react-router-dom --save

RUN npm install --save bootstrap

RUN npm install --save reactstrap react react-dom

RUN npm i axios

RUN npm install redux react-redux --save

COPY . .

EXPOSE 3000

RUN npm build

CMD [ "npm", "start" ]
