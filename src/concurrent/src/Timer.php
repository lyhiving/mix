<?php

namespace Mix\Concurrent;

/**
 * Class Timer
 * @package Mix\Core
 * @author liu,jian <coder.keda@gmail.com>
 */
class Timer
{

    /**
     * 定时器ID
     * @var int
     */
    protected $timerId;

    /**
     * 使用静态方法创建实例
     * @param mixed ...$args
     * @return $this
     */
    public static function new()
    {
        return new static();
    }

    /**
     * 在指定的时间后执行函数
     * 一次性执行
     * @param int $msec
     * @param callable $callback
     * @param mixed $params
     * @return int
     */
    public function after(int $msec, callable $callback, ... $params)
    {
        // 清除旧定时器
        $this->clear();
        // 设置定时器
        $timerId = \Swoole\Timer::after($msec, function (...$params) use ($callback) {
            if (\Swoole\Coroutine::getCid() == -1) {
                // 创建协程
                Coroutine::create($callback);
            } else {
                try {
                    // 执行闭包
                    call_user_func_array($callback, $params);
                } catch (\Throwable $e) {
                    $isMix = class_exists(\Mix::class);
                    // 错误处理
                    if (!$isMix) {
                        throw $e;
                    }
                    // Mix错误处理
                    /** @var \Mix\Console\Error $error */
                    $error = \Mix::$app->context->get('error');
                    $error->handleException($e);
                }
            }
        }, ...$params);
        // 保存id
        $this->timerId = $timerId;
        // 返回
        return $timerId;
    }

    /**
     * 设置一个间隔时钟定时器
     * 持续触发
     * @param int $msec
     * @param callable $callback
     * @param mixed $params
     * @return int
     */
    public function tick(int $msec, callable $callback, ... $params)
    {
        // 清除旧定时器
        $this->clear();
        // 设置定时器
        $timerId = \Swoole\Timer::tick($msec, function (int $timerId, ...$params) use ($callback) {
            if (\Swoole\Coroutine::getCid() == -1) {
                // 创建协程
                Coroutine::create($callback);
            } else {
                try {
                    // 执行闭包
                    call_user_func_array($callback, $params);
                } catch (\Throwable $e) {
                    $isMix = class_exists(\Mix::class);
                    // 错误处理
                    if (!$isMix) {
                        throw $e;
                    }
                    // Mix错误处理
                    /** @var \Mix\Console\Error $error */
                    $error = \Mix::$app->context->get('error');
                    $error->handleException($e);
                }
            }
        }, ...$params);
        // 保存id
        $this->timerId = $timerId;
        // 返回
        return $timerId;
    }

    /**
     * 清除定时器
     * @return bool
     */
    public function clear()
    {
        if (isset($this->timerId)) {
            return \Swoole\Timer::clear($this->timerId);
        }
        return false;
    }

    /**
     * 清除全部定时器
     * @return bool
     */
    public static function clearAll()
    {
        return \Swoole\Timer::clearAll();
    }

}
