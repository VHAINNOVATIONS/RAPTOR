using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.vista.mgt
{
    [Serializable]
    public class ArrayOfVistaRecordTO : AbstractArrayTO
    {
        public VistaRecordTO[] records;

        public ArrayOfVistaRecordTO() { }

    }
}