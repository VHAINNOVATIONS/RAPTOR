using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class NoteResult
    {
        String id;
        int totalPages = 0;
        int lastPageRecd = 0;
        String explanation;

        public NoteResult() { }

        public NoteResult(String id, int totalPages, int lastPageRecd)
        {
            Id = id;
            TotalPages = totalPages;
            LastPageRecd = lastPageRecd;
        }

        public NoteResult(String explanation)
        {
            Explanation = explanation;
        }

        public String Id
        {
            get { return id; }
            set { id = value; }
        }

        public int TotalPages
        {
            get { return totalPages; }
            set { totalPages = value; }
        }

        public int LastPageRecd
        {
            get { return lastPageRecd; }
            set { lastPageRecd = value; }
        }

        public String Explanation
        {
            get { return explanation; }
            set { explanation = value; }
        }

    }
}
