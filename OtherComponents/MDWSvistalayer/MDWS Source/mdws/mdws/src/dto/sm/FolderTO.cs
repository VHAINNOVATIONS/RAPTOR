using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class FolderTO : BaseSmTO
    {
        public string name;
        public bool isSystemFolder;

        public FolderTO() { }

        public FolderTO(mdo.domain.sm.Folder folder)
        {
            if (folder == null)
            {
                return;
            }
            this.id = folder.Id;
            this.oplock = folder.Oplock;
            this.name = folder.Name;
            isSystemFolder = folder.SystemFolder;
        }
    }
}