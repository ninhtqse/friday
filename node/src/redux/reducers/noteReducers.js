import { ADD_NEW_NOTE, REMOVE_NOTE, EDIT_NOTE } from "../const/index";
 
const noteReducers = (state = [], action) => {
  console.log('noteReducers');
  switch (action.type) {
    case ADD_NEW_NOTE:
      const generateID = new Date().getTime();
      state = [...state, { id: generateID, content: action.content }];
      return state;
    default:
      return state;
  }
};
 
export default noteReducers