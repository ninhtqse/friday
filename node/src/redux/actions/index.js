import { ADD_NEW_NOTE, REMOVE_NOTE, EDIT_NOTE } from "../const/index";
export const actAddNote = (content) => {
  return {
    type: ADD_NEW_NOTE,
    content,
  };
};