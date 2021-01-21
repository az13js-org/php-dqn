<?php
namespace Utils;

/**
 * 生成带有特定分布规律的随机数
 */
class RandomDistribution
{
    /**
     * 正态分布
     *
     * @param float $mean 平均值
     * @param float $standardDeviation 标准差
     * @return float
     */
    public static function normalDistribution(float $mean = 0, float $standardDeviation = 1) {
        // Box-Muller
        $maxRandomNumber = mt_getrandmax();
        $U1 = mt_rand() / $maxRandomNumber;
        do {
            $U2 = mt_rand() / $maxRandomNumber;
        } while (0 == $U2);
        $R = sqrt(-2 * log($U2));
        $THETA = 2 * M_PI * $U1;
        $Z = $R * cos($THETA);
        return $mean + ($Z * $standardDeviation);
    }
}