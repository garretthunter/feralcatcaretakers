<%
' -- show.asp --
' Shows a list of uploaded files
Response.Buffer = True

Dim poolDir
    poolDir = "./uploads/"

%>
<html>
<head>
	<title>List of uploaded Files</title>
	<style>
		body, input, td { font-family:verdana,arial; font-size:10pt; }
	</style>
</head>
<body>
	<p align="center">
		<b>List of uploaded Files</b><br>
		<a href="javascript:close()">Close</a>
	</p>

<%
	' File System Object
	Dim fso
		Set fso = Server.CreateObject("Scripting.FileSystemObject")
		
	' "Uploads" Folder
	Dim folder
		Set folder = fso.GetFolder(Server.MapPath(poolDir))
		
	If folder.Size > 0 Then
		Response.Write "<ul>"
		For Each file In folder.Files
				Response.Write "<li type=""circle"">"
				Response.Write "<a href=""" & poolDir & file.Name & """>"
				Response.Write "<b>" & file.Name & "</b></a>  ( Size: " & file.Size & " )  "
		Next
		Response.Write "</ul>"
	Else
		Response.Write "<ul><li type=""circle"">No Files Uploaded.</ul>"
	End If
%>
</body>
</html>
