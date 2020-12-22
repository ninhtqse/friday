FROM node:latest

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY package*.json /usr/src/app/

RUN npm install

RUN npm install react-scripts@3.0.1 -g 

COPY . .

EXPOSE 3000

RUN npm install --save bootstrap

RUN npm build

CMD [ "npm", "start" ]
