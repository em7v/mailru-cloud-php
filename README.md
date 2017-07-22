# Sdk for Cloud mail.ru
Sdk for work with cloud mail.ru


## Installation
You can install the package via composer:
``` bash
composer require friday14/cloud-mail-ru dev-master
```
## Usage

The first thing you need to do is create example class Cloud. 
``` php
$cloud = new \Friday14\Mailru\Cloud('yourLogin', 'yourPassword', 'yourDomain');
``` 
example
``` php
$cloud = new \Friday14\Mailru\Cloud('friday14', 'password', 'mail.ru');
```

### Methods

<b> files($path) </b> - Get all files which in this folder
``` php
$cloud->files('/cloud-root');
```

<b> createFile($path, $content) </b> - Create text file with your content 
``` php
$cloud->createFile('/ololo.txt','String String');
```

<b> createFolder($path) </b> - Create a folder in your Storage Cloud
``` php
$cloud->createFolder(/'new folder');
```

<b> delete($path) </b> - Delete a file or folder of your Storage
``` php 
$cloud->delete('/wallaper.jpg');
```

<b> rename($path, $name) </b> - Rename file
``` php
$cloud->rename('/cloud-folder/wallaper.jpg', 'newName.jpg');
```

<b> upload(SplFileObject $file, $filename = null) </b> - Uploads your files in Cloud (If you specify a folder in the path that does not exist, it will be created before the download)
``` php
$file = new SplFileObject($_SERVER['DOCUMENT_ROOT'] . '/teremok.jpg');
$cloud->upload($file, '/wallapers/super-teremok.jpg');
```

<b> download($path, $savePath) </b> - Download your files of Cloud
``` php 
$cloud->download('/wallapers/super-teremok.jpg', $_SERVER['DOCUMENT_ROOT'] . '/public');
```

<b> publishFile($path) </b> - Set publish flag a file or folder
``` php
$cloud->publishFile('/wallapers/super-teremok.jpg');
```

<b> getLink($path) </b> - Set publish flag and return public link a file
``` php
$cloud->getLink('/wallapers/super-teremok.jpg') // return https://cloclo4.cloud.mail.ru/thumb/xw1/wallapers/super-teremok.jpg
```
