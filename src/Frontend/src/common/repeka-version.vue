<template>
    <span>v{{version}}</span>
</template>

<script>
  export default {
    data() {
      return {
        version: REPEKA_VERSION // eslint-disable-line no-undef
      };
    }
  };
</script>
