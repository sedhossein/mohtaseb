# Mohtaseb
Mohtaseb is a simple finance system. 
It consists of two microservices which each is responsible for an specific domain. Nginx is used as the API gateway.

## 🚀 Quick start
You will be needing docker to be installed in order to be able to run this project.

Run `make up` and wait for some seconds.

## 📦 API
After running the project, a full swagger API documentation is exposed on `/` route as the frontend.
It should be accessible at [http://127.0.0.1:8080/](http://127.0.0.1:8080/). You can use the "Try it button"s on that 
page to make requests.

## 🧪 Tests
Tests can be run by executing `make test`. It will try to run all the tests, service-wise.

# Services

## 💰 Jib
Jib is a service responsible for storing financial transactions and also calculating wallet balances.

## 👨‍ Karim
Karim is the merciful service which gives gift codes. It takes care of keeping track of gift code usages,
and also notifying Jib about gift code consumptions.

## ☀️ Frontend
Serves a Swagger API documentation. The hosted documentation is available on `/` path on the main Nginx.
