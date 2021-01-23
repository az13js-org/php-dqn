<?php
namespace DQN;

use \Utils\RandomDistribution;
use \Exception;

/**
 * 模拟器
 */
class Emulator
{
    /**
     * @var int 正常状态，未持有状态
     */
    const S_NORMAL = 1;

    /**
     * @var int 持有
     */
    const S_KEEP = 2;

    /**
     * @var bool 错误动作
     */
    private $errorAction = false;

    /**
     * @var float 用于迭代计算，最后一次价格
     */
    private $lastPrice;

    /**
     * @var float[] 缓存的当前图像
     */
    private $cache = [];

    /**
     * @var int 默认大小
     */
    private $size;

    /**
     * @var int 状态
     */
    private $state;

    /**
     * @var float 可用资产总价格
     */
    private $price = 1000;

    /**
     * @var float
     */
    private $buyPrice = 0;

    /**
     * @var float|null
     */
    private $sellPrice = null;

    /** @var int */
    private $stepNumber = 1;

    /**
     * 设置模拟器
     *
     * @param int $size 游戏显示尺寸，默认400
     */
    public function __construct(int $size = 400)
    {
        if ($size < 2) {
            throw new Exception('size must > 1, your size=' . $size);
        }
        $this->size = $size;
        $this->reset();
    }

    //private $debug; // TODO 这个只是用于调试而已，后面去掉

    /**
     * 执行动作
     *
     * @param Action $a
     * @return void
     */
    public function execute(Action $a)
    {
        // TODO 这个只是用于调试而已，后面去掉 ***************
        /*if ($a->getActionType() == Action::AC_BUY) {
            $this->debug = true;
        } else {
            $this->debug = false;
        }
        return;*/ // TODO 这个只是用于调试而已，后面去掉 **********

        $this->cleanReword();
        switch ($this->state) {
            case self::S_NORMAL:
                if ($a->getActionType() == Action::AC_BUY) {
                    $this->buyPrice = $this->lastPrice;
                    $this->price -= $this->lastPrice;
                    $this->price = round($this->price, 2);
                    $this->state = self::S_KEEP;
                }
                //if ($a->getActionType() == Action::AC_SELL) {
                //    $this->errorAction = true;
                //}
                break;
            case self::S_KEEP:
                if ($a->getActionType() == Action::AC_SELL) {
                    $this->sellPrice = $this->lastPrice;
                    $this->price += $this->lastPrice;
                    $this->price = round($this->price, 2);
                    $this->state = self::S_NORMAL;
                }
                //if ($a->getActionType() == Action::AC_BUY) {
                //    $this->errorAction = true;
                //}
                break;
            default:
                throw new Exception('Your action is error!');
        }
        // 执行动作完成后，需要前进一帧
        $this->lastPrice += RandomDistribution::normalDistribution();
        //$this->lastPrice = sin($this->stepNumber / 10);
        //$this->stepNumber++;
        $this->lastPrice = round($this->lastPrice, 2);
        $this->cache[] = $this->lastPrice;
        array_shift($this->cache);
    }

    /**
     * 返回奖励
     *
     * @return float
     */
    public function getReward(): float
    {
        //return $this->debug ? 1 : -1; // TODO 这个只是用于调试而已，后面去掉
        //if ($this->errorAction) {
        //    return -100;
        //}
        if (!is_null($this->sellPrice)) {
            return round($this->sellPrice - $this->buyPrice, 2);
        }
        return 0;
    }

    /**
     * 返回当前的cache的图像
     *
     * @return Image
     */
    public function getImage(): Image
    {
        return new Image($this->cache, $this->state == self::S_KEEP, $this->buyPrice);
    }

    /**
     * 重置游戏
     *
     * @return void
     */
    public function reset()
    {
        $this->lastPrice = 0;//round(RandomDistribution::normalDistribution(250), 2);
        $this->cache = [$this->lastPrice];
        for ($i = 0; $i < $this->size - 1; $i++) {
            $this->lastPrice += RandomDistribution::normalDistribution();
            //$this->lastPrice = sin($this->stepNumber / 10);
            //$this->stepNumber++;
            $this->lastPrice = round($this->lastPrice, 2);
            $this->cache[] = $this->lastPrice;
        }
        $this->state = self::S_NORMAL;
        $this->buyPrice = 0;
        $this->sellPrice = null;
        $this->errorAction = false;
    }

    /**
     * 为了调试，返回游戏状态说明文字
     *
     * @return string
     */
    public function getGameStats(): string
    {
        if (self::S_NORMAL == $this->state) {
            return 'EMPT';
        }
        return 'KEEP';
    }

    /**
     * 清除暂时保存的奖励
     *
     * @return void
     */
    private function cleanReword()
    {
        // 购买价格不可以清除
        $this->sellPrice = null;
        $this->errorAction = false;
    }
}