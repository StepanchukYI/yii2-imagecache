yii2-imagecache
===============

Add Yii2-user to the require section of your composer.json file:
```json
{
    "require": {
        "corpsepk/yii2-imagecache": "*"
    }
}
```

Run
```php
  php composer.phar update
```

Setup config
```php
'components' => [
        ...
        'imageCache' => [
            'class' => 'corpsepk\yii2imagecache\ImageCache',
            'cachePath' => '@app/web/images/cache',
            'cacheUrl' => '/images/cache',
        ],
  ]
```


#How to use
```php
<?= Yii::$app->imageCache->img('/var/www/uploads/image.jpg','x200'), ?>
// <img src="/images/cache/x200/image.jpg" alt="">

<?= Yii::$app->imageCache->imgSrc('/var/www/uploads/image.jpg','500x'), ?>
// "/images/cache/500x/image.jpg"
```