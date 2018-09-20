<template>
    <svg class="throbber"
        viewBox="0 0 10 10">
        <circle r="4"
            cx="50%"
            cy="50%"
            stroke-width="1"
            stroke-dasharray="22"/>
    </svg>
</template>

<style lang="scss">
    .throbber {
        fill: none;
        stroke: currentColor;
        @keyframes Rotate {
            0% {
                transform: rotate(0);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        animation: linear Rotate 1s infinite;
    }
</style>
