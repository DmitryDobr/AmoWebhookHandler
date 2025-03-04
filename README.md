# AmoWebhookHandler
Простой сервер для обработки Вебхуков, приходящих из AmoCrm аккаунта. Описание составлено на момент июля 2024 года. На данный момент реализация представляет из себя 2 класса - клиент Amo для запросов и менеджер базы данных с информацией о сущностях аккаунта Amo и токенах подключения интеграции. Проект задумывался как локальное решение для одной конкретной Crm системы, поэтому мало конкретики в выполняемых функциях.

При входящем Хуке, содержащем данные POST, сервер анализирует пришедший JSON-файл и далее можно выполнять любую дополнительную логику. Например, изначально проект использовался для приема информации о смене этапов сделки и автоматическом назначении задач.

В дальнейшем проект возможно будет работать с использованием официальной php библиотеки от AmoCRM.

## 1. WebHooks
ВебХук - уведомление, которое система Amo может присылать сторонним приложениям. Для подключения в разделе AmoМаркет в меню WebHooks нужно отметить нужные события, по которым будет приходить уведомление на сервер. Подробнее о вебхуках [читайте в данном разделе](https://www.amocrm.ru/developers/content/api/webhooks).

![image](https://github.com/user-attachments/assets/31f8836a-9915-46c5-9094-3075b723381a)

## 2. Интеграция
Для возможности внесения изменений в систему по пришедшим уведомлениям нужна подключенная интеграция в Amo системе. На данный момент используется внешняя интеграция, не взаимодействующая с интерфейсом AmoCrm посредством внедрения js кода. Используются API функции [читайте в данном разделе](https://www.amocrm.ru/developers/content/crm_platform/api-reference).

![image](https://github.com/user-attachments/assets/5a483eee-a5e2-48a4-bd08-b4f7725930d8)

## 3. База данных
В проекте используется база данных для хранения токенов авторизации протокола [oAuth](https://www.amocrm.ru/developers/content/oauth/oauth). Главная таблица amo_settings имеет следующие записи, которые вносятся в соответствии с параметрами интеграции и обновляются при авторизации. Остальные таблицы содержат информацию о сущностях аккаунта (тип задач, список воронок и этапов): их идентификаторы и пр.свойства

![image](https://github.com/user-attachments/assets/435a06f5-fa9c-491a-9e51-1b191b87cc6f)

# Начало работы и авторизация
После создания интеграции и подключения домена к ВебХукам. Нужно скопировать идентификатор, секретный код и 20-минутный код доступа интеграции из настроек интеграции и перенести значения в базу данных (я использую MySQL, но можно использовать и другие). Также заполняются записи для redirect_uri (можно просто Ваш домен) и subdomain из адреса Вашей AmoCRM: https:// [subdomain] .amocrm.ru.

![image](https://github.com/user-attachments/assets/72dda965-b7e2-49f3-afe5-c5cb2e97ae0d)

Первая авторизация производится при помощи refresh_code. При успешном ответе данные о токенах переносятся в БД, после чего можно авторизовываться при помощи access_token. Можно по отдельному запросу производить авторизацию, можно на хосте поставить Cron задачу на обновление токенов. После авторизации клиент Amo поддерживает все доступные API запросы представленные в документации.

```php
  require_once 'DbHandler.php';
  require_once 'AmoClient.php';
  
  $db = new DbHandler();
  $amo = new AmoClient($db->getAllValAmo());
  
  if (!ISSET($amo->getSettings()->access_token)) {
    $db->updateAmoVals($amo->initAmoRefCode());
    echo "token init at " . date("Y-m-d H:i:s") . PHP_EOL;
  }
  else {
    // Если прошло больше времени, чем половина длительности токена
    if (time() - $amo->getSettings()->updated_at >= $amo->getSettings()->expires_in / 2) {
      $db->updateAmoVals($amo->updateAmoAccessToken());
      echo "token updated at " . date("Y-m-d H:i:s") . PHP_EOL;
    }
    else {
      echo "using current token " . date("Y-m-d H:i:s") . PHP_EOL;
    }
  }
  
  print_r($amo->getSettings());
```
