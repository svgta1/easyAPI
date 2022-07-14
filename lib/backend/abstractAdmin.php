<?php
namespace Svgta\EasyApi\backend;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\backend\Exception;

abstract class abstractAdmin extends abstractReq{
  protected function verify_insert(array $data): array{
    if(!isset($data['email']))
      throw new Exception('Email Not Set');
    if(!isset($data['given_name']))
      throw new Exception('Given_name Not Set');
    if(!isset($data['family_name']))
      throw new Exception('Family_name Not Set');

    if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
      throw new Exception('Email Not valid : ' . $data['email']);
    if(!is_string($data['given_name']))
      throw new Exception('Given_name Not valid : ' . $data['given_name']);
    if(!is_string($data['family_name']))
      throw new Exception('Family_name Not valid : ' . $data['family_name']);

    $data['createTime'] = time();
    $data['updateTime'] = time();
    $data['lastAccessTime'] = 0;
    $data['admin_id'] = utils::genUUID();
    $data['admin_secret'] = utils::password_hash($data['admin_secret']);

    return $data;
  }

  protected function verify_update(array $data): array{
    if(isset($data['email']))
    if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
      throw new Exception('Email Not valid : ' . $data['email']);

    if(isset($data['given_name']))
    if(!is_string($data['given_name']))
      throw new Exception('Given_name Not valid : ' . $data['given_name']);

    if(isset($data['family_name']))
    if(!is_string($data['family_name']))
      throw new Exception('Family_name Not valid : ' . $data['family_name']);

    $data['updateTime'] = time();

    if(isset($data['admin_secret']))
      $data['admin_secret'] = utils::password_hash($data['admin_secret']);

    return $data;
  }
}
