using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7.components
{
    public class SelectionCriterion
    {
        EncodingCharacters encChars = new EncodingCharacters();
        string fieldName = "";
        string relationalOp = "";
        string value = "";
        string relationalConjunction = "";

        public SelectionCriterion() { }

        public SelectionCriterion(string fieldName, string op, string val, string conj)
        {
            FieldName = fieldName;
            RelationalOperator = op;
            Value = val;
            RelationalConjunction = conj;
        }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public string FieldName
        {
            get { return fieldName; }
            set { fieldName = value; }
        }

        public string RelationalOperator
        {
            get { return relationalOp; }
            set { relationalOp = value; }
        }

        public string Value
        {
            get { return value; }
            set { this.value = value; }
        }

        public string RelationalConjunction
        {
            get { return relationalConjunction; }
            set { relationalConjunction = value; }
        }

        public string toComponent()
        {
            string result = FieldName +
                EncodingChars.ComponentSeparator + RelationalOperator +
                EncodingChars.ComponentSeparator + Value;
            if (RelationalConjunction != "")
            {
                result += EncodingChars.ComponentSeparator + RelationalConjunction
                    + EncodingChars.RepetitionSeparator;
            }
            return result;
        }
    }
}
