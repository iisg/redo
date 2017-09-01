interface Header {
  name: string;
  value?: string;
}

export const suppressError: Header = {
  name: 'X-Suppress-Error-Response',
  value: 'true',
};

export const debugTokenLink: Header = {
  name: 'X-Debug-Token-Link',
};
