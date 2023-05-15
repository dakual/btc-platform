FROM node:16-alpine as builder

WORKDIR /app

COPY /ft-user .

RUN npm ci 

RUN npm run build

FROM nginx:stable-alpine as production

ENV NODE_ENV production

COPY --from=builder /app/build /usr/share/nginx/html

COPY /ft-user/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]