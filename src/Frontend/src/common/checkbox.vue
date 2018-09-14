<template>
    <label class="checkbox">
        <input type="checkbox"
            :checked="value"
            v-on:change="$emit('input', $event.target.checked)">
        <div></div>
        <span v-if="label">{{label}}</span>
    </label>
</template>

<script>
  export default {
    props: {
      label: String,
      value: Boolean
    }
  };
</script>

<style lang="scss">
    @import "../styles/colors";
    @import "../styles/variables";

    .checkbox {
        cursor: pointer;
        position: initial;
        display: inline-block;
        display: inline-flex;
        align-items: center;
        margin-top: 0;
        input[type=checkbox] {
            display: none;
            & + div {
                display: inline-block;
                width: $default-gap-size;
                height: $default-gap-size;
                vertical-align: middle;
                border: 1px solid $orange;
                border-radius: 25%;
            }
            &:checked + div {
                background: $orange;
                background: radial-gradient($orange 40%, transparent 40%);
            }
        }
        span {
            margin-left: $default-gap-size / 4;
            vertical-align: middle;
        }
    }
</style>
