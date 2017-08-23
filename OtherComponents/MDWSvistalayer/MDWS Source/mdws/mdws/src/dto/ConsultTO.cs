using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ConsultTO : OrderTO
    {
        public TaggedText toService;
        public string title;

        public ConsultTO() { }

        public ConsultTO(Consult mdo)
        {
            this.id = mdo.Id;
            this.timestamp = mdo.Timestamp.ToString("yyyyMMdd.HHmmss");
            this.toService = new TaggedText(mdo.Service);
            this.status = mdo.Status;
            this.title = mdo.Title;
            this.text = mdo.Text;
        }
    }
}
