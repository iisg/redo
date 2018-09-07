<template>
    <svg class="icon"
        viewBox="0 0 1 1">
        <use :href="'/files/icons.svg#' + name"></use>
    </svg>
</template>

<script>
  export default {
    props: ['name']
  };
</script>

<style lang="scss">
    .icon {
        display: none; // Used to temporarily hide icons on Internet Explorer because it does not support this way of displaing them.
        display: initial; //
        height: 1em;
        vertical-align: -0.15em;
        fill: currentColor;
        &[size="0.5"] {
            height: 0.5em;
            vertical-align: 0.075em;
        }
        &[size="0.75"] {
            height: 0.75em;
            vertical-align: -0.05em;
        }
        &[size="1.25"] {
            height: 1.25em;
            vertical-align: -0.25em;
        }
        &[size="1.5"] {
            height: 1.5em;
            vertical-align: -0.4em;
        }
        &[size="1.75"] {
            height: 1.75em;
            vertical-align: -0.5em;
        }
        &[size="2"] {
            height: 2em;
            vertical-align: -0.65em;
        }
        &[size="2.5"] {
            height: 2.5em;
            vertical-align: -0.85em;
        }
        &[size="3"] {
            height: 3em;
            vertical-align: -1.15em;
        }
    }
</style>
