define(["dojo", "dojo/_base/declare"], function (dojo, declare) {
  return declare("forestshuffle.utils", null, {
    ucfirst(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
  });
});
