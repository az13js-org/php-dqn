<?php
namespace DQN;

/**
 * 集合
 */
class Transition
{
    /** @var Sequence */
    private $firstSequence;

    /** @var Sequence */
    private $lastSequence;

    /** @var Action */
    private $action;

    /** @var float */
    private $reward;

    /**
     * 初始化序列片
     *
     * @param Sequence $omiga1
     * @param Action $a1
     * @param float $r1
     * @param Sequence $omiga2
     */
    public function __construct(Sequence $omiga1, Action $a1, float $r1, Sequence $omiga2)
    {
        $this->firstSequence = $omiga1;
        $this->action = $a1;
        $this->reward = $r1;
        $this->lastSequence = $omiga2;
    }

    /**
     * 是否游戏结束
     *
     * @return bool
     */
    public function isTerminateStep(): bool
    {
        return false;
    }

    /**
     * 是返回动作
     *
     * @return Action
     */
    public function getAction(): Action
    {
        return $this->action;
    }

    /**
     * 返回奖励
     *
     * @return float
     */
    public function getReward(): float
    {
        return $this->reward;
    }

    /**
     * 返回第一个序列
     *
     * @return Sequence
     */
    public function getFirstOmiga(): Sequence
    {
        return $this->firstSequence;
    }

    /**
     * 返回最后一个序列
     *
     * @return Sequence
     */
    public function getLastOmiga(): Sequence
    {
        return $this->lastSequence;
    }
}