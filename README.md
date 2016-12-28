# English
## codeigniter3-sentry-core-replacing-version
[Codeigniter](https://codeigniter.com/) 3.x version with Sentry, Core Class Replacing, Costom core class

Please refer to [Link](https://codeigniter.com/user_guide/general/core_classes.html) for reference to Core Class Replacing.

How To Install

1. install Codeigniter 3.x
2. install sentry by composer
    `composer require "sentry/sentry"`
3. adding config information
    Copy the sentry setting value in the `/application/config/config.php` file and insert it into your `/application/config/config.php` file.
4. change config information
    * `$config['log_threshold']` is codigniter logging level(Recommand 1 or 0)
    * Set the value of `$config['log_path']` to `'sentry'`.
    * `$config['sentry_client']` is your sentry DSN(such as [Sentry](https://sentry.io))
    * `$config['sentry_config']` specifies the user option settings. Please refer to the following article: [link](https://github.com/getsentry/raven-php#configuration) and [sentry doc](https://docs.sentry.io/clients/php/config/)
5. place `application/core/Log.php` into your `application/core/` folder 
6. Use the same as codeigniter's log_message! Enjoy!

※ If the speed is too slow, try lowering log_threshold or setting curl_method in sentry_config! [curl_method info](https://github.com/getsentry/raven-php#curl_method)

* * *

# 한국어(korean)
## codeigniter3-sentry-core-replacing-version
[Codeigniter](https://codeigniter.com/) 3.x 버전과 Sentry를 Core class Replacing으로 만든 Custom Core Class입니다.

Core Class Replacing 관련한 내용은 [링크](http://www.ciboard.co.kr/user_guide/kr/general/core_classes.html)를 참고하시기 바랍니다.

설치방법

1. Codeigniter 3.x버전을 설치합니다.
2. composer를 사용해서 sentry를 설치해줍니다.
    `composer require "sentry/sentry"`
3. `/application/config/config.php` 파일에 있는 sentry 부분을 복사해서 적용하실 config파일에 삽입합니다.
4. 설정값을 변경합니다.
    * `$config['log_threshold']`는 Codeigniter에서 사용하는 설정과 동일합니다.(추천은 1 - error 이나 0 - off 입니다.)
    * `$config['log_path']`를 문자열 `'sentry'`로 설정해주세요.(ex. `$config['log_path'] = 'sentry'`)
    * `$config['sentry_client']`에 사용하실 DSN를 입력해주세요. Sentry 사이트에 가시면 확인하실 수 있습니다.(such as [Sentry](https://sentry.io))
    * `$config['sentry_config']` sentry에는 추가로 설정할 수 있는 설정 값들이 있습니다. 원하는 내용을 링크에서 확인하셔서 입력하시면 됩니다. [link](https://github.com/getsentry/raven-php#configuration) and [sentry doc](https://docs.sentry.io/clients/php/config/)
5. `/application/core/Log.php` 파일을 당신의 `/application/core/`폴더에 복사해주세요.
6. 기존에 사용하시던 codeigniter의 log_message와 동일하게 사용하시면 됩니다.

※ 만약에 속도가 너무 느리다면 log_threshold를 낮춰보거나 sentry_config에 curl_method를 설정해보세요! [curl_method info](https://github.com/getsentry/raven-php#curl_method)