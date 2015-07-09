using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DrgTO : AbstractTO
    {
        public string id;
        public string description;

        public DrgTO() { }

        public DrgTO(Drg mdoDrg)
        {
            this.id = mdoDrg.Id;
            this.description = mdoDrg.Description;
        }
    }
}
