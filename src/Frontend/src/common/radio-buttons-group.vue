<template>
    <div class="radio-buttons-group">
        <label v-for="label in labels"
            :key="label">
            <input type="radio"
                :checked="label == value"
                :name="name"
                :value="label"
                v-on:change="$emit('input', $event.target.value)">
            <div></div>
            <span>{{label}}</span>
        </label>
    </div>
</template>

<script>
  export default {
    props: {
      name: String,
      labels: Array,
      value: String
    }
  };
</script>

<style lang="scss">
    @import "../styles/colors";
    @import "../styles/variables";

    .radio-buttons-group {
        display: inline-block;
        label {
            cursor: pointer;
            display: inline-block;
            display: inline-flex;
            align-items: center;
            &:not(:last-child) {
                margin-right: $default-gap-size / 2;
            }
            input[type=radio] {
                display: none;
                & + div {
                    display: inline-block;
                    width: $default-gap-size;
                    height: $default-gap-size;
                    vertical-align: middle;
                    border: 1px solid $orange;
                    border-radius: 50%;
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
    }
</style>
