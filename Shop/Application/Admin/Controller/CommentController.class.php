<?php

namespace Admin\Controller;

use Think\AjaxPage;
use Think\Model;
use Think\Page;

class CommentController extends BaseController {


    public function index(){
        $this->display();
    }

    public function detail(){
        $id = I('get.id');
        $res = M('comment')->where(array('comment_id'=>$id))->find();
        if(!$res){
            exit($this->error('不存在该评论'));
        }
        $reply = M('comment')->where(array('parent_id'=>$id))->order('add_time desc')->select(); // 评论回复列表
         
        $this->assign('comment',$res);
        $this->assign('reply',$reply);
        $this->display();
    }

    /**
     * 添加评论
     */
    public function addComment(){
        if(IS_POST){
            $id = I('post.comment_id');
            $comment = M('comment')->where(array('comment_id'=>$id))->find();
            $add['parent_id'] = $id;
            $add['content'] = I('post.content');
            $add['goods_id'] = $comment['goods_id'];
            $add['add_time'] = time();
            $add['username'] = 'admin';
            $row =  M('comment')->add($add);
            if($row){
                $res['status'] = 1;
                $res['msg']    = '添加成功';
                $res['data']   = $add;
                $res['data']['add_time'] = date('Y-m-d H:i:s',$add['add_time']);    // 将时间戳转化为日期
            }else{
                $res['status'] = 0;
                $res['msg'] = '添加失败';
            }
            $this->ajaxReturn($res);
        }
    }

    public function ajaxindex(){
        $model = M('comment');
        $username = I('nickname','','trim');
        $content = I('content','','trim');
        $where=' parent_id = 0';
        if($username){
            $where .= " AND username='$username'";
        }
        if($content){
            $where .= " AND content like '%{$content}%'";
        }        
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,16);
        $show = $Page->show();
                
        $comment_list = $model->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        if(!empty($comment_list))
        {
            $goods_id_arr = get_arr_column($comment_list, 'goods_id');
            $goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
        }
        $this->assign('goods_list',$goods_list);
        $this->assign('comment_list',$comment_list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }
    
    public function ask_list(){
    	$this->display();
    }
    
    public function ajax_ask_list(){
    	$model = M('goods_consult');
    	$username = I('nickname','','trim');
    	$content = I('content','','trim');
    	$where=' parent_id = 0';
    	if($username){
    		$where .= " AND username='$username'";
    	}
    	if($content){
    		$where .= " AND content like '%{$content}%'";
    	}
        $count = $model->where($where)->count();        
        $Page  = new AjaxPage($count,16);
        $show = $Page->show();            	
    	
        $comment_list = $model->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); 
    	if(!empty($comment_list))
    	{
    		$goods_id_arr = get_arr_column($comment_list, 'goods_id');
    		$goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
    	}
    	$consult_type = array(0=>'默认咨询',1=>'商品咨询',2=>'支付咨询',3=>'配送',4=>'售后');
    	$this->assign('consult_type',$consult_type);
    	$this->assign('goods_list',$goods_list);
    	$this->assign('comment_list',$comment_list);
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
    
    public function consult_info(){
    	$id = I('get.id');
    	$res = M('goods_consult')->where(array('id'=>$id))->find();
    	if(!$res){
    		exit($this->error('不存在该咨询'));
    	}
    	if(IS_POST){
    		$add['parent_id'] = $id;
    		$add['content'] = I('post.content');
    		$add['goods_id'] = $res['goods_id'];
                $add['consult_type'] = $res['consult_type'];
    		$add['add_time'] = time();    		
    		$add['is_show'] = 1;   	
    		$row =  M('goods_consult')->add($add);
    		if($row){
    			$this->success('添加成功');
    		}else{
    			$this->error('添加失败');
    		}
    		exit;    	
    	}
    	$reply = M('goods_consult')->where(array('parent_id'=>$id))->select(); // 咨询回复列表   	 
    	$this->assign('comment',$res);
    	$this->assign('reply',$reply);
    	$this->display();
    }
    public function ask_handle(){
    	$type = I('post.type');
    	$selected_id = I('post.selected');        
    	if(!in_array($type,array('del','show','hide')) || !$selected_id)
    		$this->error('操作完成');
    
        $selected_id = implode(',',$selected_id);
    	if($type == 'del'){
    		//删除咨询
    		$where .= "( id IN ({$selected_id}) OR parent_id IN ({$selected_id})) ";
    		$row = M('goods_consult')->where($where)->delete();
    	}
    	if($type == 'show'){
    		$row = M('goods_consult')->where("id IN ({$selected_id})")->save(array('is_show'=>1));
    	}
    	if($type == 'hide'){
    		$row = M('goods_consult')->where("id IN ({$selected_id})")->save(array('is_show'=>0));
    	}    		
    	$this->success('操作完成');
    }
}