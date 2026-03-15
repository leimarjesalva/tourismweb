FROM php:8.2-cli
COPY backend/ /app/backend/
COPY frontend/ /app/frontend/
WORKDIR /app/backend
RUN docker-php-ext-install mysqli pdo pdo_mysql
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app/frontend"]