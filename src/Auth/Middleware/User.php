<?php

namespace Auth\Middleware;

use Auth\Storage\StorageInterface;

class User
{
    private $storage;
    private $config;

    /**
     * Constructor
     *
     * @param array $config Configuration for the Opauth instance
     */
    public function __construct(StorageInterface $Storage, array $Config)
    {
        $this->storage  = $Storage;
        $this->config   = $Config;
    }

    public function __invoke($req, $res, $next)
    {
        if(!empty(($user = $_SESSION['auth']['user'])) 
            && (empty($user['role']) || empty($user['role_hash'])
            || $user['role_hash'] !== sha1($this->config['secretKey'].date('i')[0].$user['role']))){
                
            // var_dump($user);exit(__FILE__.'::'.__LINE__);
            
            $data = call_user_func_array(
                [$this->config['mapper'][strtolower($user['provider'])],'map'],
                [$user]
            );
            // $u = $this->storage->fetch('users', $data['users']['email'], 'email');
            $u = $this->storage->userByEmail($data['users']['email']);
            // var_dump($u);exit(__FILE__.'::'.__LINE__);
            
            // if the user does not exists then try persist it
            if(!$u){
                $data['users']['registrationDate'] = date('Y-m-d H:i:s');
                $data['users']['external'] = 1;
                // var_dump($data['users']);exit(__FILE__.'::'.__LINE__);
                $id = $this->storage->insert('users',$data['users']);
                
                // store user meta data if any
                if(!empty($id) && !empty($data['user_meta'])){
                    $this->storage->insertMeta('user_meta', 
                        ['userId'=>$id, 'authorId'=> 0, 'state' => 1], 
                        $data['user_meta']);
                }else{
                    return $next($req, $res->withStatus(403), 'This is a restricted area!');
                }
            }else{
                if(!isset($u['state']) || empty($u['state'])){
                    return $next($req, $res->withStatus(403), 'This is a restricted area!');
                }
                $id = $u['id'];
                $_SESSION['auth']['user']['role'] = $u['role'];
                // cache role for max 10 min
                $_SESSION['auth']['user']['role_hash'] = sha1($this->config['secretKey'].date('i')[0].$u['role']);
            }
            
            $_SESSION['auth']['user']['luid'] = $id;
            
        }
        
        // inject author id
        if(!empty(($luid = $_SESSION['auth']['user']['luid']))){
            if ('POST' === $req->getMethod()) {
                $post = $req->getParsedBody();
                $post['authorId'] = $luid;
                $req = $req->withParsedBody($post);
            }
        }
        
        
        return $next($req, $res);
    }
}