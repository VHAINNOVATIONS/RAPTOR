using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class NoteResultTO : AbstractTO
    {
        public string id;
        public int totalPages = 0;
        public int lastPageRecd = 0;
        public string explanation;

        public NoteResultTO() { }

        public NoteResultTO(NoteResult mdoResult)
        {
            this.id = mdoResult.Id;
            this.totalPages = mdoResult.TotalPages;
            this.lastPageRecd = mdoResult.LastPageRecd;
            this.explanation = mdoResult.Explanation;
        }
    }
}
