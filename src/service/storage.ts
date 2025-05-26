export const getFromLocalStorage = (key: string): object | null => {
  const data = localStorage.getItem(key)

  return data ? JSON.parse(data) : null
}

export const setToLocalStorage = (key: string, data: object) => {
  localStorage.setItem(key, JSON.stringify(data))
}
