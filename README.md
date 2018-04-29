# Sdk для работы с облаком  mail.ru

## Установка с помощью composer:
``` bash
composer require friday14/cloud-mail-ru dev-master
```

## Использование

``` php
$cloud = new \Friday14\Mailru\Cloud('yourLogin', 'yourPassword', 'yourDomain');
``` 
Пример:
``` php
$cloud = new \Friday14\Mailru\Cloud('friday14', 'password', 'mail.ru');
```

### Методы

**files($path)**  - Получить все файлы из заданного каталога
``` php
$cloud->files('/cloud-root');
```

**createFile($path, $content)** - Создать файл с заданным контентом
``` php
$cloud->createFile('/ololo.txt','Hello World');
```

**createFolder($path)** - Создать папку в облаке
``` php
$cloud->createFolder('/foldername');
```

**delete($path)** - Удалить папку/файл
``` php 
$cloud->delete('/wallaper.jpg');
```

**rename($path, $name)** - Переименновать папку/файл
``` php
$cloud->rename('/cloud-folder/wallaper.jpg', 'newName.jpg');
```

 **upload(SplFileObject $file, $filename = null)**  - Uploads your files in Cloud (If you specify a folder in the path that does not exist, it will be created before the download)
``` php
    $file = new SplFileObject($_SERVER['DOCUMENT_ROOT'] . '/teremok.jpg');
    $cloud->upload($file, '/wallapers/super-teremok.jpg');
```

 **download($path, $savePath)**  - Загрузить файл.
``` php 
  $cloud->download('/wallapers/super-teremok.jpg', $_SERVER['DOCUMENT_ROOT'] . '/folder/filename.format');
```

 **publishFile($path)**  - Сделать файл общедоступным
``` php
  $cloud->publishFile('/wallapers/super-teremok.jpg');
```

 **getLink($path)**  - Сделать файл общедоступным и получить ссылку на файл
``` php
  $cloud->getLink('/wallapers/super-teremok.jpg') // return https://cloclo4.cloud.mail.ru/thumb/xw1/wallapers/super-teremok.jpg
```