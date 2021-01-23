<?php
namespace DQN;

/**
 * 深度强化学习算法
 */
class Algorithm
{
    /** @var ReplayMemory */
    private $D;

    /** @var NeuralNetwork */
    private $Q;

    /** @var NeuralNetwork */
    private $hatQ;

    /** @var Image[] */
    private $x = [];

    /** @var Sequence[] */
    private $s = [];

    /** @var Sequence[] */
    private $omiga = [];

    /** @var Action[] */
    private $a = [];

    /** @var Emulator */
    private $emulator;

    /** @var float[] */
    private $r = [];

    /**
     * @param float $N 回放内存的大小
     * @param array $neuralNetworkLayersConfig 神经网络每层细胞数配置
     * @param int $episodes 游戏次数
     * @param int $T 一场游戏的最大步数
     * @param float $epsilon 随机选择一个动作的概率
     * @param float $gamme 强化学习使用的参数，越小，越依赖当前的动作产生的回报
     * @param int $C Q经过多少次miniBatch训练，就把Q的参数复制给hatQ
     */
    public function __construct(float $N, array $neuralNetworkLayersConfig, int $episodes, int $T, float $epsilon, float $gamma, int $C)
    {
        $step = 0;
        $this->emulator = new Emulator();
        $this->D = new ReplayMemory($N);
        $this->Q = new NeuralNetwork($neuralNetworkLayersConfig);
        $this->Q->randomWeights();
        $this->hatQ = new NeuralNetwork($neuralNetworkLayersConfig);
        $this->hatQ->copyWeightsFrom($this->Q);
        for ($episode = 1; $episode <= $episodes; $episode++) {
            $rewardTotalThisEpisode = 0;
            $this->emulator->reset();
            $this->x[1] = $this->emulator->getImage();
            $this->s[1] = new Sequence(new Element(null, null, $this->x[1]));
            $this->omiga[1] = $this->preprocess($this->s[1]);
            for ($t = 1; $t <= $T; $t++) {
                if (mt_rand() / mt_getrandmax() <= $epsilon) {
                    $this->a[$t] = Action::selectRandomAction();
                } else {
                    $this->a[$t] = $this->argmax($this->omiga[$t], $this->Q);
                }
                $this->emulator->execute($this->a[$t]);
                $this->r[$t] = $this->emulator->getReward();
                if ($this->r[$t] > 0)
                echo '    ' . $this->r[$t] . PHP_EOL;
                elseif ($this->r[$t] < 0)
                echo '   ' . $this->r[$t] . PHP_EOL;
                $rewardTotalThisEpisode += $this->r[$t];
                $this->x[$t + 1] = $this->emulator->getImage();
                $this->s[$t + 1] = new Sequence(new Element($this->s[$t], $this->a[$t], $this->x[$t + 1]));
                $this->omiga[$t + 1] = $this->preprocess($this->s[$t + 1]);
                $this->D->store(new Transition($this->omiga[$t], $this->a[$t], $this->r[$t], $this->omiga[$t + 1]));
                $miniBatch = $this->D->randomSampleTransitions();
                $y = [];
                $x = [];
                foreach ($miniBatch as $transition) {
                    if ($transition->isTerminateStep()) {
                        $y[] = $transition->getReward();
                    } else {
                        $y[] = $transition->getReward() + $gamma * $this->argmaxq($transition->getLastOmiga(), $this->hatQ);
                    }
                    $x[] = [
                        'sequence' => $transition->getFirstOmiga(),
                        'action' => $transition->getAction(),
                    ];
                }
                $this->Q->gradientDescent($x, $y);
                $step++;
                if (0 == $step % $C && $step > 0) {
                    $this->hatQ->copyWeightsFrom($this->Q);
                }
            }
            echo 'episode: ' . $episode . ', ';
            echo "reward: $rewardTotalThisEpisode" . PHP_EOL;
            $this->Q->save('data' . DIRECTORY_SEPARATOR . 'episode_' . $episode . '_reward_' . $rewardTotalThisEpisode . '.dat');
        }
    }

    /**
     * 对序列预处理成适合神经网络接收的输入
     *
     * @param Sequence $st
     * @return Sequence
     */
    private function preprocess(Sequence $st): Sequence
    {
        // 暂时就这么返回回去
        return $st;
    }

    /**
     * 利用动作价值函数 Q 获得给定输入下会产生最大 q 值的动作
     *
     * @param Sequence $input
     * @param NeuralNetwork $Q
     * @return Action
     */
    private function argmax(Sequence $input, NeuralNetwork $Q): Action
    {
        $arr1 = $input->toArray();
        $action = new Action(Action::AC_NOTHING);
        $a = [
            new Action(Action::AC_BUY),
            new Action(Action::AC_SELL),
            $action,
        ];
        $yMin = -100;
        foreach ($a as $ae) {
            $y = $Q->run(['sequence' => $input, 'action' => $ae]);
            if ($y > $yMin) {
                $yMin = $y;
                $action = $ae;
            }
        }
        return $action;
    }

    /**
     * 利用动作价值函数 Q 获得给定输入下会产生最大 q 值的动作的预计回报
     *
     * @param Sequence $input
     * @param NeuralNetwork $Q
     * @return float
     */
    private function argmaxq(Sequence $input, NeuralNetwork $Q): float
    {
        $arr1 = $input->toArray();
        $action = new Action(Action::AC_NOTHING);
        $a = [
            new Action(Action::AC_BUY),
            new Action(Action::AC_SELL),
            $action,
        ];
        $yMin = -100;
        foreach ($a as $ae) {
            $y = $Q->run(['sequence' => $input, 'action' => $ae]);
            if ($y > $yMin) {
                $yMin = $y;
                $action = $ae;
            }
        }
        return $yMin;
    }
}