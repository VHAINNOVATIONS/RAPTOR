using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7
{
    public class EncodingCharacters
    {
        const int CMP_SEP_CHAR = 0;
        const int REP_SEP_CHAR = 1;
        const int ESCAPE_CHAR = 2;
        const int SUB_SEP_CHAR = 3;

        char fieldSep;
        char[] encChars;

        public EncodingCharacters()
        {
            FieldSeparator = '|';
            this.encChars = new char[4];
            ComponentSeparator = '^';
            RepetitionSeparator = '~';
            EscapeCharacter = '\\';
            SubcomponentSeparator = '&';
        }

        public EncodingCharacters(char fieldSeparator, string encodingCharacters)
        {
            FieldSeparator = (fieldSeparator == '\0' ? '|' : fieldSeparator);
            this.encChars = new char[4];
            if (encodingCharacters == null)
            {
                ComponentSeparator = '^';
                RepetitionSeparator = '~';
                EscapeCharacter = '\\';
                SubcomponentSeparator = '&';
            }
            else
            {
                for (int i = 0; i < 4; i++)
                {
                    this.encChars[i] = encodingCharacters[i];
                }
            }
        }

        public EncodingCharacters(
            char fieldSeparator,
            char componentSeparator,
            char repetitionSeparator,
            char escapeCharacter,
            char subcomponentSeparator)
        {
            FieldSeparator = fieldSeparator;
            ComponentSeparator = componentSeparator;
            RepetitionSeparator = repetitionSeparator;
            EscapeCharacter = escapeCharacter;
            SubcomponentSeparator = subcomponentSeparator;
        }

        /** copies contents of "other" */
        public EncodingCharacters(EncodingCharacters other)
        {
            FieldSeparator = other.FieldSeparator;
            this.encChars = new char[4];
            ComponentSeparator = other.ComponentSeparator;
            RepetitionSeparator = other.RepetitionSeparator;
            EscapeCharacter = other.EscapeCharacter;
            SubcomponentSeparator = other.SubcomponentSeparator;
        }

        public char FieldSeparator
        {
            get { return fieldSep; }
            set { fieldSep = value; }
        }

        public char ComponentSeparator
        {
            get { return encChars[CMP_SEP_CHAR]; }
            set { encChars[CMP_SEP_CHAR] = value; }
        }

        public char RepetitionSeparator
        {
            get { return encChars[REP_SEP_CHAR]; }
            set { encChars[REP_SEP_CHAR] = value; }
        }

        public char EscapeCharacter
        {
            get { return encChars[ESCAPE_CHAR]; }
            set { encChars[ESCAPE_CHAR] = value; }
        }

        public char SubcomponentSeparator
        {
            get { return encChars[SUB_SEP_CHAR]; }
            set { encChars[SUB_SEP_CHAR] = value; }
        }

        /**
         * Returns the encoding characters (not including field separator)
         * as a string.
         */
        public string toString()
        {
            string result = "";
            for (int i = 0; i < this.encChars.Length; i++)
            {
                result += this.encChars[i];
            }
            return result;
        }

        public Object clone()
        {
            return new EncodingCharacters(this);
        }

    //    public bool equals(Object o) 
    //    {
    //        if (o.GetType().IsInstanceOfType(EncodingCharacters))
    //    {
    //        EncodingCharacters other = (EncodingCharacters) o;
    //        if (this.getFieldSeparator() == other.getFieldSeparator()
    //            && this.getComponentSeparator() == other
    //            .getComponentSeparator()
    //            && this.getEscapeCharacter() == other.getEscapeCharacter()
    //            && this.getRepetitionSeparator() == other
    //            .getRepetitionSeparator()
    //            && this.getSubcomponentSeparator() == other
    //            .getSubcomponentSeparator()) 
    //        {
    //            return true;
    //        } 
    //        else 
    //        {
    //            return false;
    //        }
    //    } 
    //    else 
    //    {
    //        return false;
    //    }
    //}

        ///** @see java.lang.Object#hashCode */
        //public int hashCode()
        //{
        //    return 7 * (int)this.getComponentSeparator()
        //        * (int)this.getEscapeCharacter()
        //        * (int)this.getFieldSeparator()
        //        * (int)this.getRepetitionSeparator()
        //        * (int)this.getSubcomponentSeparator();
        //}
    }
}
