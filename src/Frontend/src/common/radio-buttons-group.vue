<template>
    <div class="radio-buttons-group">
        <label v-for="option in values"
            :key="option.label">
            <input type="radio"
                :checked="option == value"
                :name="name"
                @change="$emit('input', option)">
            <div></div>
            <span>{{option.label}}</span>
        </label>
    </div>
</template>

<script>
  export default {
    props: {
      name: String,
      values: Array,
      value: {}
    },
    mounted() {
      if (!this.value) {
        this.$emit('input', this.values[0]);
      }
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
