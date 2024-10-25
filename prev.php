/**
     * error response
     */
    public function errorResponse($errors,$acidErrors=false,$message=false)
    {
        Yii::$app->response->statusCode = 422;
        foreach($errors as $key=>$value){
            $errors[$key]=$value[0];
        }
        if(is_array($acidErrors)){
            foreach($acidErrors['acidErrorModel'] as $key=>$value){
                foreach($value->getErrors() as $k=>$value){
                    $error[$acidErrors['errorKey']][$key][$k] = $value[0];
                }
            }
        }
        if(isset($error)){
            $errors = array_merge($errors,$error);
        }
        
        $array['errorPayload']['errors']=$errors;

        if($message){
            $array['errorPayload'] = array_merge($array['errorPayload']['errors'], $this->toastResponse(
                [
                    'statusCode'=>422,
                    'message'=>$message ? $message : 'Some data could not be validated',
                    'theme'=>'danger'
                ]
            )['toastPayload']);

        }
        return $array;
    }













 FROM vaultke/php8-fpm-nginx

# Install packages
RUN apk update && apk add --no-cache \
    supervisor \
    busybox-suid \
    bash \
    tini \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY . /var/www/html

RUN chmod -R 777 /var/www/html

# Supervisor configuration directory
RUN mkdir -p /etc/supervisor/conf.d/

COPY supervisor/email_queue.conf /etc/supervisor/conf.d/email_queue.conf
COPY supervisor/email_worker.conf /etc/supervisor/conf.d/email_worker.conf


COPY crontab /etc/crontabs/root

RUN chmod 0644 /etc/crontabs/root

# Use tini to start supervisord in the foreground
ENTRYPOINT ["/sbin/tini", "--"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
