using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7
{
    public interface IEncodingCharacters
    {
        char getFieldSeparator();
        char getComponentSeparator();
        char getRepetitionSeparator();
        char getEscapeCharacter();
        char getSubcomponentSeparator();
    }
}
