using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.vista.mgt
{
    public class VistaRecordTO : AbstractTO
    {
        public VistaFileTO file;
        public VistaFieldTO[] fields;
        public String ien;
        public String iens;
        public String siteId;

        public VistaRecordTO() { }
    }
}