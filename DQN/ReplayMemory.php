<?php
namespace DQN;

use \Exception;

/**
 * 回放内存
 */
class ReplayMemory
{
    /** @var int 尺寸 */
    private $size = 0;

    /** @var int 当前存储的长度 */
    private $length = 0;

    /** @var Transition[] 存储的片 */
    private $transitions = [];

    /**
     * @param float $N 回放内存的大小
     */
    public function __construct(float $N)
    {
        if ($N < 2) {
            throw new Exception('Error, $N must >= 2, N=' . $N);
        }
        $this->size = $N;
    }

    /**
     * 不重复随机采样多个 Transition
     *
     * @return Transition[]
     */
    public function randomSampleTransitions(): array
    {
        if (0 == $this->length) {
            throw new Exception('ReplayMemory is empty.');
        }
        $temp = $this->transitions;
        shuffle($temp);
        if ($this->length < 50) {
            return $temp;
        }
        $result = [];
        for ($i = 0; $i < 50; $i++) {
            $result[] = $temp[$i];
        }
        return $result;
    }

    /**
     * 存储一个 Transition
     *
     * @param Transition
     * @return void
     */
    public function store(Transition $transition)
    {
        $this->transitions[] = $transition;
        $this->length++;
        if ($this->length > $this->size) {
            array_shift($this->transitions);
            $this->length = $this->size;
        }
    }
}